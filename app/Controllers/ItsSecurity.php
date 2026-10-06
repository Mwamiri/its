<?php
namespace App\Controllers;
use App\Libraries\{Totp, Qr, Sla};
use App\Models\{UserModel, SettingModel};

class ItsSecurity extends ItsBase {
    private function me() {
        $u = $this->user();
        return $u ? (new UserModel())->find($u['id']) : null;
    }
    public function index() {
        if (!$this->user()) return redirect()->to(base_url('its-login'));
        $me = $this->me();
        $setup = null;
        if (empty($me['totp_enabled'])) {
            $secret = session('totp_setup');
            if (!$secret) { $secret = Totp::newSecret(); session()->set('totp_setup', $secret); }
            $uri = Totp::uri($secret, $me['username'], SettingModel::get('company_name', 'IT Support') ?: 'IT Support');
            $setup = ['secret' => $secret, 'qr' => (new Qr())->svg($uri)];
        }
        $admin = $this->user()['role'] === 'admin';
        return view('itsupport/security', [
            'title' => 'Security', 'me' => $me, 'setup' => $setup, 'codes' => session()->getFlashdata('codes'),
            'admin' => $admin,
            'policy' => $admin ? ['require_2fa_admin' => SettingModel::get('require_2fa_admin', '0'), 'session_timeout' => SettingModel::get('session_timeout', '120'), 'sla_business_hours' => SettingModel::get('sla_business_hours', '0'), 'sla' => Sla::targets()] : null,
        ]);
    }
    public function enable() {
        if (!$this->user()) return redirect()->to(base_url('its-login'));
        $secret = (string) session('totp_setup');
        if ($secret === '' || !Totp::verify($secret, (string) $this->request->getPost('code'))) {
            return redirect()->to(base_url('its-security'))->with('err', 'That code did not match. Check the time on your phone and try again.');
        }
        [$plain, $hashes] = Totp::newRecoveryCodes();
        (new UserModel())->update($this->user()['id'], ['totp_secret' => Totp::seal($secret), 'totp_enabled' => 1, 'recovery_codes' => $hashes]);
        session()->remove('totp_setup');
        $this->audit('2fa_enabled', 'auth', $this->user()['username']);
        return redirect()->to(base_url('its-security'))->with('ok', 'Two-factor authentication is on. Save your recovery codes now; they are shown only once.')->with('codes', $plain);
    }
    public function disable() {
        if (!$this->user()) return redirect()->to(base_url('its-login'));
        $me = $this->me();
        if (!password_verify((string) $this->request->getPost('password'), $me['password'])) return redirect()->to(base_url('its-security'))->with('err', 'Password is incorrect.');
        (new UserModel())->update($me['id'], ['totp_secret' => null, 'totp_enabled' => 0, 'recovery_codes' => null]);
        $this->audit('2fa_disabled', 'auth', $me['username']);
        return redirect()->to(base_url('its-security'))->with('ok', 'Two-factor authentication has been turned off.');
    }
    public function password() {
        if (!$this->user()) return redirect()->to(base_url('its-login'));
        $me = $this->me();
        $new = (string) $this->request->getPost('new_password');
        if (!password_verify((string) $this->request->getPost('current_password'), $me['password'])) return redirect()->to(base_url('its-security'))->with('err', 'Current password is incorrect.');
        if (strlen($new) < 8 || $new !== (string) $this->request->getPost('confirm_password')) return redirect()->to(base_url('its-security'))->with('err', 'The new password must be at least 8 characters and match the confirmation.');
        (new UserModel())->update($me['id'], ['password' => password_hash($new, PASSWORD_DEFAULT)]);
        $this->audit('password_changed', 'auth', $me['username']);
        return redirect()->to(base_url('its-security'))->with('ok', 'Password updated.');
    }
    public function policy() {
        if ($r = $this->needAdmin()) return $r;
        SettingModel::put('require_2fa_admin', $this->request->getPost('require_2fa_admin') ? '1' : '0');
        SettingModel::put('sla_business_hours', $this->request->getPost('sla_business_hours') ? '1' : '0');
        SettingModel::put('session_timeout', (string) max(5, min(1440, (int) $this->request->getPost('session_timeout'))));
        $sla = [];
        foreach (array_keys(Sla::DEFAULTS) as $p) $sla[$p] = [max(1, (int) $this->request->getPost("resp_$p")), max(1, (int) $this->request->getPost("res_$p"))];
        SettingModel::put('sla_hours', json_encode($sla));
        $this->audit('policy_saved', 'security');
        return redirect()->to(base_url('its-security'))->with('ok', 'Policies saved.');
    }
    public function resetUser2fa($id) {
        if ($r = $this->needAdmin()) return $r;
        (new UserModel())->update((int) $id, ['totp_secret' => null, 'totp_enabled' => 0, 'recovery_codes' => null, 'failed_logins' => 0, 'locked_until' => null]);
        $this->audit('2fa_reset', 'auth', (string) $id);
        return redirect()->to(base_url('its-admin'))->with('ok', 'Two-factor reset and any lockout cleared for that user.');
    }
}