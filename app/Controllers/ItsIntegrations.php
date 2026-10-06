<?php
namespace App\Controllers;
use App\Libraries\{Perm, Webhooks};

class ItsIntegrations extends ItsBase {
    private function back(string $kind, string $msg) { return redirect()->to(base_url('its-admin?tab=' . $kind))->with($kind === 'permissions' ? 'ok' : 'ok', $msg); }

    public function savePermissions() {
        if ($r = $this->needAdmin()) return $r;
        $posted = $this->request->getPost('perm') ?: [];
        $tbl = $this->db->table('role_permissions');
        foreach (Perm::ROLES as $role) foreach (array_keys(Perm::MODULES) as $mod) {
            $lvl = (int) ($posted[$role][$mod] ?? 0);
            $lvl = max(0, min(2, $lvl));
            $tbl->replace(['role' => $role, 'module' => $mod, 'level' => $lvl]);
        }
        $this->audit('permissions_saved', 'security');
        return $this->back('permissions', 'Permissions saved.');
    }

    public function addKey() {
        if ($r = $this->needAdmin()) return $r;
        $name = trim((string) $this->request->getPost('name'));
        if ($name === '' || mb_strlen($name) > 120) return redirect()->to(base_url('its-admin?tab=integrations'))->with('err', 'Enter a name for the key.');
        $key = 'itk_' . bin2hex(random_bytes(20));
        $this->db->table('api_keys')->insert(['name' => $name, 'key_prefix' => substr($key, 0, 8), 'key_hash' => hash('sha256', $key), 'created_at' => date('Y-m-d H:i:s')]);
        $this->audit('api_key_created', 'integrations', $name);
        return redirect()->to(base_url('its-admin?tab=integrations'))->with('ok', 'API key created. Copy it now; it is shown only once.')->with('newKey', $key);
    }

    public function revokeKey($id) {
        if ($r = $this->needAdmin()) return $r;
        $this->db->table('api_keys')->where('id', (int) $id)->delete();
        $this->audit('api_key_revoked', 'integrations', (string) $id);
        return $this->back('integrations', 'API key revoked.');
    }

    public function addHook() {
        if ($r = $this->needAdmin()) return $r;
        $url = trim((string) $this->request->getPost('url'));
        if (!filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'https://') || mb_strlen($url) > 500) return redirect()->to(base_url('its-admin?tab=integrations'))->with('err', 'The webhook URL must be a valid https:// address.');
        $this->db->table('webhooks')->insert(['url' => $url, 'secret' => bin2hex(random_bytes(16)), 'active' => 1]);
        $this->audit('webhook_added', 'integrations', $url);
        return $this->back('integrations', 'Webhook added.');
    }

    public function deleteHook($id) {
        if ($r = $this->needAdmin()) return $r;
        $this->db->table('webhooks')->where('id', (int) $id)->delete();
        return $this->back('integrations', 'Webhook removed.');
    }

    public function testHook() {
        if ($r = $this->needAdmin()) return $r;
        Webhooks::fire('test.ping', ['message' => 'Webhook test from IT Support']);
        return $this->back('integrations', 'Test event sent; see the status column.');
    }
}