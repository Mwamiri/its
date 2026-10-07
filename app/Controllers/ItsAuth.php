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
            if ($company !== '' && $username !== '' && strlen($password) >= 8) {
                $userId = (new UserModel())->insert(['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT), 'name' => $username, 'role' => 'admin', 'active' => 1]);
                SettingModel::put('company_name', $company);
                SettingModel::put('company_email', (string) $this->request->getPost('email'));
                SettingModel::put('cron_token', bin2hex(random_bytes(16)));
                session()->set('its_user', ['id' => $userId, 'username' => $username, 'name' => $username, 'role' => 'admin']);
                session()->regenerate();
                return redirect()->to(base_url('its-dashboard'));
            }
            return view('itsupport/install', ['error' => 'Enter a company name, username, and password with at least 8 characters.']);
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
        if ((new UserModel())->countAll() === 0) return redirect()->to(base_url('its-install'));
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
                // Second factor: authenticator app first, otherwise an emailed code / one-time link (v3.5).
                if (!empty($u['totp_enabled']) || $this->emailFactor($u)) {
                    $pending = ['id' => $u['id'], 'at' => time(), 'tries' => 0, 'method' => !empty($u['totp_enabled']) ? 'totp' : 'email'];
                    if ($pending['method'] === 'email' && ($err = $this->sendEmailCode($u, $pending)) !== null) session()->setFlashdata('err', $err);
                    session()->set('its_pending', $pending);
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
    /** Email codes are available when the user switched them on and has a valid address. */
    private function emailFactor(array $u): bool {
        return !empty($u['email_2fa']) && filter_var((string) ($u['email'] ?? ''), FILTER_VALIDATE_EMAIL) !== false;
    }
    /**
     * Emails a 6-digit code and a one-time sign-in link (both valid 10 minutes). Returns an error message or null.
     * At most 5 emails per sign-in attempt and one every 45 seconds.
     */
    private function sendEmailCode(array $u, array &$pending): ?string {
        if (($pending['sends'] ?? 0) >= 5) return 'Too many codes were sent. Please sign in again in a few minutes.';
        if (!empty($pending['sent_at']) && time() - (int) $pending['sent_at'] < 45) return 'A code was sent a moment ago. Please check your inbox (and spam folder) before asking for another.';
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = bin2hex(random_bytes(32));
        $exp = date('Y-m-d H:i:s', time() + 600);
        if ($this->db->tableExists('login_tokens')) {
            $this->db->table('login_tokens')->where('user_id', $u['id'])->where('used_at', null)->delete();
            $this->db->table('login_tokens')->insert(['user_id' => $u['id'], 'token_hash' => hash('sha256', $token), 'purpose' => '2fa_link', 'expires_at' => $exp, 'ip_address' => $this->request->getIPAddress(), 'created_at' => date('Y-m-d H:i:s')]);
        }
        $pending['code_hash'] = hash('sha256', $code . '|' . $u['id'] . '|' . $pending['at']);
        $pending['code_exp'] = time() + 600;
        $pending['sent_at'] = time();
        $pending['sends'] = ($pending['sends'] ?? 0) + 1;
        $company = (string) SettingModel::get('company_name', 'IT Support');
        $link = base_url('its-2fa-link/' . $token);
        $html = '<div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;color:#0f172a"><h2 style="margin:0 0 8px">' . esc($company) . ' sign-in</h2><p>Use this code to finish signing in:</p>'
            . '<p style="font-size:32px;font-weight:700;letter-spacing:8px;background:#f1f5f9;border-radius:12px;padding:14px;text-align:center;margin:12px 0">' . $code . '</p>'
            . '<p>Or sign in with one click:</p><p><a href="' . esc($link) . '" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 20px;border-radius:10px;font-weight:700">Sign in now</a></p>'
            . '<p style="color:#64748b;font-size:13px">The code and link expire in 10 minutes and work once. If you did not try to sign in, change your password and tell your administrator. Request from IP ' . esc($this->request->getIPAddress()) . '.</p></div>';
        try {
            $ok = (new \App\Libraries\MailLib())->send([(string) $u['email']], $code . ' is your ' . $company . ' sign-in code', $html);
        } catch (\Throwable $e) {
            $ok = false;
        }
        $this->audit($ok ? '2fa_email_sent' : '2fa_email_failed', 'auth', $u['username']);
        return $ok ? null : 'We could not send the sign-in email. Use your authenticator app or a recovery code, or ask an administrator to check the mail settings.';
    }
    /** Switch the pending sign-in to an emailed code (or send a new one). */
    public function twoFactorEmail() {
        $pending = session('its_pending');
        if (!$pending || time() - (int) $pending['at'] > 900) { session()->remove('its_pending'); return redirect()->to(base_url('its-login')); }
        $u = (new UserModel())->find($pending['id']);
        if (!$u || !$this->emailFactor($u)) return redirect()->to(base_url('its-2fa'))->with('err', 'Email codes are not enabled for this account.');
        $pending['method'] = 'email';
        $err = $this->sendEmailCode($u, $pending);
        session()->set('its_pending', $pending);
        return redirect()->to(base_url('its-2fa'))->with($err ? 'err' : 'ok', $err ?? 'We emailed a sign-in code and link to ' . $this->maskEmail((string) $u['email']) . '.');
    }
    /** One-time link from the sign-in email: completes the sign-in on whichever device opens it. */
    public function twoFactorLink($token) {
        $token = (string) $token;
        $row = preg_match('/^[a-f0-9]{64}$/', $token) && $this->db->tableExists('login_tokens')
            ? $this->db->table('login_tokens')->where('token_hash', hash('sha256', $token))->where('purpose', '2fa_link')->get()->getRowArray() : null;
        if (!$row || $row['used_at'] !== null || strtotime($row['expires_at']) < time()) {
            return redirect()->to(base_url('its-login'))->with('err', 'That sign-in link has expired or was already used. Please sign in again.');
        }
        $this->db->table('login_tokens')->where('id', $row['id'])->update(['used_at' => date('Y-m-d H:i:s')]);
        $u = (new UserModel())->where('id', $row['user_id'])->where('active', 1)->first();
        if (!$u || (!empty($u['locked_until']) && strtotime($u['locked_until']) > time())) return redirect()->to(base_url('its-login'))->with('err', 'This account cannot sign in right now.');
        $this->audit('login_email_link', 'auth', $u['username']);
        return $this->finishLogin($u);
    }
    private function maskEmail(string $e): string {
        [$n, $d] = array_pad(explode('@', $e, 2), 2, '');
        return mb_substr($n, 0, 2) . str_repeat('•', max(1, mb_strlen($n) - 2)) . '@' . $d;
    }
    public function twoFactor() {
        $pending = session('its_pending');
        if (!$pending || time() - (int) $pending['at'] > 900) { session()->remove('its_pending'); return redirect()->to(base_url('its-login')); }
        $users = new UserModel();
        $u = $users->find($pending['id']);
        $error = null;
        if (!$u || (empty($u['totp_enabled']) && !$this->emailFactor($u))) { session()->remove('its_pending'); return redirect()->to(base_url('its-login')); }
        $method = ($pending['method'] ?? 'totp') === 'email' ? 'email' : 'totp';
        if ($this->request->getMethod() === 'POST') {
            $code = preg_replace('/\s+/', '', (string) $this->request->getPost('code'));
            $ok = false;
            if ($method === 'email') {
                $ok = !empty($pending['code_hash']) && time() <= (int) ($pending['code_exp'] ?? 0)
                    && hash_equals($pending['code_hash'], hash('sha256', $code . '|' . $u['id'] . '|' . $pending['at']));
                // One email = one sign-in: if its link was already used (on any device), the code is spent too.
                if ($ok && $this->db->tableExists('login_tokens') && !$this->db->table('login_tokens')->where('user_id', $u['id'])->where('used_at', null)->where('expires_at >', date('Y-m-d H:i:s'))->countAllResults()) {
                    session()->remove('its_pending');
                    return redirect()->to(base_url('its-login'))->with('err', 'This sign-in was already completed with the link from the same email. Sign in again if you need another session.');
                }
                if ($ok && $this->db->tableExists('login_tokens')) $this->db->table('login_tokens')->where('user_id', $u['id'])->where('used_at', null)->update(['used_at' => date('Y-m-d H:i:s')]);
            } elseif (!empty($u['totp_enabled'])) {
                try { $step = \App\Libraries\Totp::matchStep(\App\Libraries\Totp::unseal($u['totp_secret']), $code); } catch (\Throwable $e) { $step = null; }
                // Reject a code from a time step that was already used (replay); the column exists once the v2.1.1 migration has run.
                $tracksSteps = array_key_exists('totp_last_step', $u);
                if ($step !== null && (!$tracksSteps || $step > (int) $u['totp_last_step'])) {
                    if ($tracksSteps) $users->update($u['id'], ['totp_last_step' => $step]);
                    $ok = true;
                }
            }
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
        return view('itsupport/two_factor', ['error' => $error, 'emailOption' => $this->emailFactor($u)]);
    }
    public function logout() {
        $this->audit('logout', 'auth');
        session()->remove(['its_user', 'its_pending', 'its_last']);
        session()->regenerate();
        return redirect()->to(base_url('its-login'));
    }
}