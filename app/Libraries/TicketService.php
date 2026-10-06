<?php

namespace App\Libraries;

use App\Models\TicketModel;

/** Shared ticket lifecycle rules: SLA dates, resolution stamps, and webhook events. */
class TicketService
{
    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    public const TYPES = ['incident', 'request', 'problem', 'change'];

    public static function create(array $in): int
    {
        $priority = in_array($in['priority'] ?? '', self::PRIORITIES, true) ? $in['priority'] : 'medium';
        $type = in_array($in['ticket_type'] ?? '', self::TYPES, true) ? $in['ticket_type'] : 'incident';
        $db = \Config\Database::connect();
        $db->table('sequences')->where('name', 'ticket')->set('current_value', 'current_value + 1', false)->update();
        $v = (int) $db->table('sequences')->where('name', 'ticket')->get()->getRow()->current_value;
        $number = 'TCK-' . date('Ymd') . '-' . str_pad((string) $v, 4, '0', STR_PAD_LEFT);
        $row = [
            'ticket_number' => $number, 'client_id' => (int) $in['client_id'], 'subject' => (string) $in['subject'],
            'description' => (string) ($in['description'] ?? ''), 'priority' => $priority, 'status' => 'new',
            'ticket_type' => $type, 'visit_date' => $in['visit_date'] ?? date('Y-m-d'),
        ] + Sla::dueFor($priority);
        $id = (new TicketModel())->insert($row);
        Webhooks::fire('ticket.created', ['id' => (int) $id, 'number' => $number, 'client_id' => $row['client_id'], 'subject' => $row['subject'], 'priority' => $priority, 'type' => $type]);
        return (int) $id;
    }

    public static function setStatus(int $id, string $status): bool
    {
        $m = new TicketModel();
        $t = $m->find($id);
        if (! $t || $t['status'] === $status) return false;
        $data = ['status' => $status];
        $closing = in_array($status, ['completed', 'closed'], true);
        if ($closing && empty($t['resolved_at'])) $data['resolved_at'] = date('Y-m-d H:i:s');
        if (! $closing) $data['resolved_at'] = null;
        $m->update($id, $data);
        Webhooks::fire('ticket.status_changed', ['id' => $id, 'number' => $t['ticket_number'], 'from' => $t['status'], 'to' => $status]);
        return true;
    }

    public static function staffResponded(int $id): void
    {
        $m = new TicketModel();
        $t = $m->find($id);
        if ($t && empty($t['first_response_at'])) $m->update($id, ['first_response_at' => date('Y-m-d H:i:s')]);
    }
}