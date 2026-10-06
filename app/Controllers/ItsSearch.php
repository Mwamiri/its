<?php
namespace App\Controllers;

use App\Libraries\Perm;

class ItsSearch extends ItsBase {
    public function index() {
        if ($r = $this->needLogin()) return $this->response->setStatusCode(401)->setJSON([]);
        $q = trim((string) $this->request->getGet('q'));
        if (mb_strlen($q) < 2) return $this->response->setJSON([]);
        $role = (string) $this->user()['role'];
        $out = [];
        $add = function (string $module, string $type, array $rows, callable $fmt) use (&$out, $role) {
            if (!Perm::can($role, $module, 1)) return;
            foreach ($rows as $row) $out[] = ['type' => $type] + $fmt($row);
        };
        $b = $this->db->table('tickets');
        $add('tickets', 'Ticket', $b->groupStart()->like('ticket_number', $q)->orLike('subject', $q)->groupEnd()->orderBy('id', 'DESC')->limit(6)->get()->getResultArray(), fn($t) => ['label' => $t['ticket_number'] . ' ' . $t['subject'], 'url' => base_url('its-tickets-view/' . $t['id'])]);
        $add('clients', 'Client', $this->db->table('clients')->like('name', $q)->limit(5)->get()->getResultArray(), fn($c) => ['label' => $c['name'], 'url' => base_url('its-clients')]);
        $add('assets', 'Asset', $this->db->table('assets')->groupStart()->like('name', $q)->orLike('serial_number', $q)->groupEnd()->limit(5)->get()->getResultArray(), fn($a) => ['label' => $a['name'] . ($a['serial_number'] ? ' (' . $a['serial_number'] . ')' : ''), 'url' => base_url('its-assets')]);
        $add('kb', 'Article', $this->db->table('kb_articles')->like('title', $q)->limit(5)->get()->getResultArray(), fn($k) => ['label' => $k['title'], 'url' => base_url('its-kb?q=' . rawurlencode($k['title']))]);
        return $this->response->setJSON($out);
    }
}