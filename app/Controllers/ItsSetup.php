<?php

namespace App\Controllers;

use App\Libraries\SetupKey;
use CodeIgniter\Exceptions\PageNotFoundException;

class ItsSetup extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $key = (string) env('its.setupKey', '');
        if (preg_match('/\A[0-9a-f]{64}\z/', $key) !== 1) {
            throw PageNotFoundException::forPageNotFound();
        }
        if (ENVIRONMENT === 'production' && ! $this->request->isSecure()) {
            return $this->response->setStatusCode(403)->setBody('Setup requires HTTPS.');
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setHeader('Cache-Control', 'no-store')->setBody(view('itsupport/setup'));
        }

        if (! SetupKey::valid($key, (string) $this->request->getPost('setup_key'))) {
            return $this->response->setStatusCode(403)->setHeader('Cache-Control', 'no-store')
                ->setBody(view('itsupport/setup', ['error' => 'Invalid setup key.']));
        }

        $company = trim((string) $this->request->getPost('company'));
        $username = trim((string) $this->request->getPost('username'));
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        if ($company === '' || mb_strlen($company) > 190 || $username === '' || mb_strlen($username) > 100
            || strlen($password) < 8 || strlen($password) > 72
            || ($email !== '' && (strlen($email) > 190 || ! filter_var($email, FILTER_VALIDATE_EMAIL)))) {
            return $this->response->setStatusCode(422)->setHeader('Cache-Control', 'no-store')
                ->setBody(view('itsupport/setup', ['error' => 'Check the company, username, email and password fields.']));
        }

        $lock = fopen(WRITEPATH . 'setup.lock', 'c');
        if ($lock === false) {
            log_message('error', 'Setup could not open its lock file.');
            return $this->setupError('Setup cannot access writable storage. Check server permissions.');
        }
        try {
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                return $this->setupError('Another setup is already in progress. Try again shortly.');
            }
            $db = \Config\Database::connect();
            foreach (['users', 'settings', 'tickets', 'clients', 'assets', 'tasks'] as $table) {
                if (! $db->tableExists($table)) {
                    return $this->setupError('Base database tables are missing. Import itsupport_schema.sql using phpMyAdmin first.');
                }
            }
            if ($db->table('users')->countAllResults() !== 0) {
                return $this->setupError('Setup is unavailable because this database already contains users.');
            }
            \Config\Services::migrations()->latest();
            $db->transBegin();
            $db->table('users')->insert([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'name' => $username,
                'role' => 'admin',
                'active' => 1,
            ]);
            foreach (['company_name' => $company, 'company_email' => $email, 'cron_token' => bin2hex(random_bytes(16))] as $name => $value) {
                $db->table('settings')->replace(['setting_key' => $name, 'setting_value' => $value]);
            }
            if (! $db->transStatus() || ! $db->transCommit()) {
                throw new \RuntimeException('Could not commit setup database transaction.');
            }
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->transRollback();
            }
            log_message('error', 'Setup failed: {message}', ['message' => $e->getMessage()]);
            return $this->setupError('Setup failed. Check the server application logs and database configuration before retrying.');
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return $this->response->setHeader('Cache-Control', 'no-store')->setBody(view('itsupport/setup', ['success' => true]));
    }

    private function setupError(string $message)
    {
        return $this->response->setStatusCode(503)->setHeader('Cache-Control', 'no-store')
            ->setBody(view('itsupport/setup', ['error' => $message]));
    }
}
