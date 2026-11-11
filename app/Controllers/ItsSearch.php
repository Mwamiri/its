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
        $add = function (string $type, string $module, array $rows, callable $fmt) use (&$out, $role) {
            if (!Perm::can($role, $module, 1)) return;
            foreach ($rows as $row) $out[] = ['type' => $type] + $fmt($row);
        };
        $add('ticket', 'tickets', $this->db->table('tickets')->groupStart()->like('ticket_number', $q)->orLike('subject', $q)->groupEnd()->orderBy('id', 'DESC')->limit(6)->get()->getResultArray(), fn($t) => ['label' => $t['subject'], 'sub' => $t['ticket_number'], 'url' => base_url('its-tickets-view/' . $t['id'])]);
        $add('client', 'clients', $this->db->table('clients')->like('name', $q)->limit(5)->get()->getResultArray(), fn($c) => ['label' => $c['name'], 'sub' => null, 'url' => base_url('its-clients-view/' . $c['id'])]);
        $add('asset', 'assets', $this->db->table('assets')->groupStart()->like('name', $q)->orLike('serial_number', $q)->groupEnd()->limit(5)->get()->getResultArray(), fn($a) => ['label' => $a['name'], 'sub' => $a['serial_number'] ?: null, 'url' => base_url('its-assets-view/' . $a['id'])]);
        $add('article', 'kb', $this->db->table('kb_articles')->like('title', $q)->limit(4)->get()->getResultArray(), fn($k) => ['label' => $k['title'], 'sub' => null, 'url' => base_url('its-kb?q=' . rawurlencode($k['title']))]);
        if ($this->db->tableExists('extensions')) $add('extension', 'extensions', $this->db->table('extensions')->groupStart()->like('name', $q)->orLike('description', $q)->groupEnd()->where('status', 'approved')->limit(4)->get()->getResultArray(), fn($e) => ['label' => $e['name'], 'sub' => \App\Controllers\ItsExtensions::PLATFORMS[$e['platform']] ?? $e['platform'], 'url' => base_url('its-extensions')]);
        return $this->response->setJSON($out);
    }
}