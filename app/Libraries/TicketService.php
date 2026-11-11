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
        $number = Sequence::next('ticket', 'TCK-');
        $row = [
            'ticket_number' => $number, 'client_id' => (int) $in['client_id'], 'subject' => (string) $in['subject'],
            'description' => (string) ($in['description'] ?? ''), 'priority' => $priority, 'status' => 'new',
            'ticket_type' => $type, 'visit_date' => $in['visit_date'] ?? date('Y-m-d'),
        ] + Sla::dueFor($priority);
        // Optional link to one of the same client's assets (v3); ignored if it belongs to someone else.
        $assetId = (int) ($in['asset_id'] ?? 0);
        if ($assetId && $db->fieldExists('asset_id', 'tickets') && $db->table('assets')->where('id', $assetId)->where('client_id', $row['client_id'])->countAllResults()) $row['asset_id'] = $assetId;
        $id = (new TicketModel())->insert($row);
        Webhooks::fire('ticket.created', ['id' => (int) $id, 'number' => $number, 'client_id' => $row['client_id'], 'subject' => $row['subject'], 'priority' => $priority, 'type' => $type]);
        return (int) $id;
    }

    public static function setStatus(int $id, string $status): bool
    {
        $m  = new TicketModel();
        $t  = $m->find($id);
        if (! $t || $t['status'] === $status) return false;
        $data    = ['status' => $status];
        $closing = in_array($status, ['completed', 'closed'], true);
        if ($closing && empty($t['resolved_at'])) $data['resolved_at'] = date('Y-m-d H:i:s');
        if (! $closing) $data['resolved_at'] = null;
        // CSAT: generate token on first close if not already sent
        if ($closing && empty($t['csat_sent']) && \App\Models\SettingModel::get('csat_enabled', '1') === '1') {
            $token = bin2hex(random_bytes(20));
            $data['rating_token'] = $token;
            $data['csat_sent']    = 1;
            $m->update($id, $data);
            self::sendCsatEmail($t, $token);
        } else {
            $m->update($id, $data);
        }
        Webhooks::fire('ticket.status_changed', ['id' => $id, 'number' => $t['ticket_number'], 'from' => $t['status'], 'to' => $status]);
        return true;
    }

    private static function sendCsatEmail(array $ticket, string $token): void
    {
        try {
            $db = \Config\Database::connect();
            $client = $db->table('clients')->select('name, email')->where('id', $ticket['client_id'])->get()->getRowArray();
            if (empty($client['email'])) return;
            $company = \App\Models\SettingModel::get('company_name', 'IT Support');
            $rateUrl = base_url('its-rate/' . $token);
            $html = '<p>Hello ' . esc($client['name']) . ',</p>'
                  . '<p>Your support ticket <strong>' . esc($ticket['ticket_number']) . ': ' . esc($ticket['subject']) . '</strong> has been resolved.</p>'
                  . '<p>We\'d love to hear how we did. Please take a moment to rate your experience:</p>'
                  . '<p style="margin:20px 0">'
                  . '<a href="' . $rateUrl . '?r=5" style="padding:10px 16px;background:#16a34a;color:#fff;border-radius:8px;text-decoration:none;margin-right:6px">⭐ Excellent</a>'
                  . '<a href="' . $rateUrl . '?r=4" style="padding:10px 16px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;margin-right:6px">👍 Good</a>'
                  . '<a href="' . $rateUrl . '?r=3" style="padding:10px 16px;background:#ca8a04;color:#fff;border-radius:8px;text-decoration:none;margin-right:6px">😐 OK</a>'
                  . '<a href="' . $rateUrl . '?r=2" style="padding:10px 16px;background:#dc2626;color:#fff;border-radius:8px;text-decoration:none">👎 Poor</a>'
                  . '</p>'
                  . '<p style="font-size:12px;color:#666">Or <a href="' . $rateUrl . '">leave a comment</a>.</p>'
                  . '<p style="font-size:12px;color:#666">— ' . esc($company) . ' support team</p>';
            (new \App\Libraries\MailLib())->send([$client['email']], 'How did we do? – Ticket ' . $ticket['ticket_number'], $html);
        } catch (\Throwable $e) {
            log_message('error', 'CSAT email failed: ' . $e->getMessage());
        }
    }

    /** Notify all watchers of a ticket about an update (excludes the author). */
    public static function notifyWatchers(int $ticketId, string $subject, string $message, int $excludeUserId = 0): void
    {
        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('ticket_watchers')) return;
            $watchers = $db->table('ticket_watchers tw')
                ->select('u.email, u.name')
                ->join('users u', 'u.id = tw.user_id')
                ->where('tw.ticket_id', $ticketId)
                ->where('u.active', 1)
                ->where('u.id !=', $excludeUserId)
                ->get()->getResultArray();
            if (! $watchers) return;
            $ticket  = (new TicketModel())->find($ticketId);
            $viewUrl = base_url('its-tickets-view/' . $ticketId);
            $html    = '<p>' . nl2br(esc($message)) . '</p>'
                     . '<p><a href="' . $viewUrl . '">View ticket ' . esc($ticket['ticket_number'] ?? '') . '</a></p>';
            $emails  = array_column($watchers, 'email');
            (new \App\Libraries\MailLib())->send($emails, $subject, $html);
        } catch (\Throwable $e) {
            log_message('error', 'Watcher notification failed: ' . $e->getMessage());
        }
    }

    public static function staffResponded(int $id): void
    {
        $m = new TicketModel();
        $t = $m->find($id);
        if ($t && empty($t['first_response_at'])) $m->update($id, ['first_response_at' => date('Y-m-d H:i:s')]);
    }
}