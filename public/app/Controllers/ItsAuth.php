<?php
namespace App\Controllers;
use App\Models\UserModel;
use App\Models\SettingModel;
class ItsAuth extends ItsBase {
    public function install() {
        if ((new UserModel())->countAll() > 0) return redirect()->to('/its-login');
        if ($this->request->getMethod() === 'post') {
            $company = $this->request->getPost('company');
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            if ($company && $username && strlen($password) >= 6) {
                (new UserModel())->insert(['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT), 'name' => $username, 'role' => 'admin', 'active' => 1]);
                SettingModel::set('company_name', $company);
                SettingModel::set('company_email', (string) $this->request->getPost('email'));
                SettingModel::set('cron_token', bin2hex(random_bytes(16)));
                session()->set('its_user', ['id' => 1, 'username' => $username, 'name' => $username, 'role' => 'admin']);
                return redirect()->to('/its-dashboard');
            }
        }
        return view('itsupport/install');
    }
    public function login() {
        if ($this->user()) return redirect()->to('/its-dashboard');
        $error = null;
        if ($this->request->getMethod() === 'post') {
            $u = (new UserModel())->where('username', $this->request->getPost('username'))->where('active', 1)->first();
            if ($u && password_verify($this->request->getPost('password') ?? '', $u['password'])) {
                session()->set('its_user', ['id' => $u['id'], 'username' => $u['username'], 'name' => $u['name'], 'role' => $u['role']]);
                session()->regenerate();
                $this->audit('login', 'auth', $u['username']);
                return redirect()->to('/its-dashboard');
            }
            $error = 'Invalid credentials.';
        }
        return view('itsupport/login', ['error' => $error]);
    }
    public function logout() {
        $this->audit('logout', 'auth');
        session()->remove('its_user');
        session()->regenerate();
        return redirect()->to('/its-login');
    }
}