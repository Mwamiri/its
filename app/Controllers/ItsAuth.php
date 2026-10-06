<?php
namespace App\Controllers;
use App\Models\UserModel;
use App\Models\SettingModel;
use App\Models\LoginAttemptModel;
class ItsAuth extends ItsBase {
    public function install() {
        if ((new UserModel())->countAll() > 0) return redirect()->to(base_url('its-login'));
        if ($this->request->getMethod() === 'POST') {
            $company = trim((string) $this->request->getPost('company'));
            $username = trim((string) $this->request->getPost('username'));
            $password = (string) $this->request->getPost('password');
            if ($company !== '' && $username !== '' && strlen($password) >= 6) {
                $userId = (new UserModel())->insert(['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT), 'name' => $username, 'role' => 'admin', 'active' => 1]);
                SettingModel::put('company_name', $company);
                SettingModel::put('company_email', (string) $this->request->getPost('email'));
                SettingModel::put('cron_token', bin2hex(random_bytes(16)));
                session()->set('its_user', ['id' => $userId, 'username' => $username, 'name' => $username, 'role' => 'admin']);
                session()->regenerate();
                return redirect()->to(base_url('its-dashboard'));
            }
            return view('itsupport/install', ['error' => 'Enter a company name, username, and password with at least 6 characters.']);
        }
        return view('itsupport/install', ['error' => null]);
    }
    private function finishLogin(array $u) {
        session()->remove('its_pending');
        session()->set('its_user', ['id' => $u['id'], 'username' => $u['username'], 'name' => $u['name'], 'role' => $u['role'], 'client_id' => $u['client_id'] ?? null]);
        session()->set('its_last', time());
        session()->regenerate();
        (new UserModel())->update($u['id'], ['failed_logins' => 0, 'locked_until' => null, 'last_login' => date('Y-m-d H:i:s')]);
        (new LoginAttemptModel())->where('ip_address', $this->request->getIPAddress())->delete();
        $this->audit('login', 'auth', $u['username']);
        return redirect()->to($this->homeFor(session('its_user')));
    }
    public function login() {
        if ($this->user()) return redirect()->to($this->homeFor($this->user()));
        $error = null;
        if ($this->request->getMethod() === 'POST') {
            $ip = $this->request->getIPAddress();
            $attempts = new LoginAttemptModel();
            $attempts->where('attempted_at <', date('Y-m-d H:i:s', strtotime('-15 minutes')))->delete();
            $username = trim((string) $this->request->getPost('username'));
            $users = new UserModel();
            $u = $users->where('username', $username)->where('active', 1)->first();
            if ($attempts->where('ip_address', $ip)->countAllResults() >= 10) {
                $error = 'Too many failed attempts. Try again in 15 minutes.';
            } elseif ($u && !empty($u['locked_until']) && strtotime($u['locked_until']) > time()) {
                $error = 'This account is temporarily locked after repeated failures. Try again later.';
            } elseif ($u && password_verify((string) $this->request->getPost('password'), $u['password'])) {
                if (!empty($u['totp_enabled'])) {
                    session()->set('its_pending', ['id' => $u['id'], 'at' => time(), 'tries' => 0]);
                    return redirect()->to(base_url('its-2fa'));
                }
                return $this->finishLogin($u);
            } else {
                $attempts->insert(['username' => $username, 'ip_address' => $ip, 'attempted_at' => date('Y-m-d H:i:s')]);
                if ($u) {
                    $fails = (int) $u['failed_logins'] + 1;
                    $users->update($u['id'], ['failed_logins' => $fails, 'locked_until' => $fails >= 5 ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null]);
                    if ($fails >= 5) $this->audit('account_locked', 'auth', $username);
                }
                $error = 'Invalid credentials.';
            }
        }
        return view('itsupport/login', ['error' => $error]);
    }
    public function twoFactor() {
        $pending = session('its_pending');
        if (!$pending || time() - (int) $pending['at'] > 300) { session()->remove('its_pending'); return redirect()->to(base_url('its-login')); }
        $users = new UserModel();
        $u = $users->find($pending['id']);
        $error = null;
        if (!$u || empty($u['totp_enabled'])) { session()->remove('its_pending'); return redirect()->to(base_url('its-login')); }
        if ($this->request->getMethod() === 'POST') {
            $code = trim((string) $this->request->getPost('code'));
            $ok = false;
            try { $ok = \App\Libraries\Totp::verify(\App\Libraries\Totp::unseal($u['totp_secret']), $code); } catch (\Throwable $e) { $ok = false; }
            if (!$ok && ($left = \App\Libraries\Totp::useRecovery($u['recovery_codes'], $code)) !== null) {
                $users->update($u['id'], ['recovery_codes' => $left]);
                $this->audit('recovery_code_used', 'auth', $u['username']);
                $ok = true;
            }
            if ($ok) return $this->finishLogin($u);
            $pending['tries']++;
            if ($pending['tries'] >= 5) {
                $users->update($u['id'], ['locked_until' => date('Y-m-d H:i:s', strtotime('+15 minutes'))]);
                $this->audit('account_locked', 'auth', $u['username'] . ' (2FA)');
                session()->remove('its_pending');
                return redirect()->to(base_url('its-login'))->with('err', 'Too many incorrect codes. The account is locked for 15 minutes.');
            }
            session()->set('its_pending', $pending);
            $error = 'That code is not valid.';
        }
        return view('itsupport/two_factor', ['error' => $error]);
    }    public function logout() {
        $this->audit('logout', 'auth');
        session()->remove(['its_user', 'its_pending', 'its_last']);
        session()->regenerate();
        return redirect()->to(base_url('its-login'));
    }
}