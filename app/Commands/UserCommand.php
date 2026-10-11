<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Emergency user management from the command line — use when nobody can sign in.
 *
 * php spark its:user list
 * php spark its:user create <username> <role>  [--name=] [--email=] [--password=]
 * php spark its:user password <username>       [--password=]
 * php spark its:user unlock <username>
 */
class UserCommand extends BaseCommand
{
    protected $group       = 'ITSupport';
    protected $name        = 'its:user';
    protected $description = 'List users, create one, change a password or unlock an account.';
    protected $usage       = 'its:user list|create|password|unlock [username] [role]';

    private const ROLES = ['admin', 'tech', 'client'];

    public function run(array $params)
    {
        $model = new UserModel();

        switch ($params[0] ?? 'list') {
            case 'list':
                foreach ($model->orderBy('role')->orderBy('username')->findAll() as $u) {
                    CLI::write(
                        str_pad($u['username'], 20) .
                        str_pad($u['role'], 10) .
                        ((int) $u['active'] ? 'active' : CLI::color('disabled', 'red')) .
                        '  ' . ($u['name'] ?? '')
                    );
                }
                break;

            case 'create':
                [$username, $role] = [(string) ($params[1] ?? ''), (string) ($params[2] ?? 'tech')];
                if ($username === '' || ! in_array($role, self::ROLES, true)) {
                    CLI::error('Give a username and a role: ' . implode(', ', self::ROLES));
                    return EXIT_ERROR;
                }
                $password = (string) (CLI::getOption('password') ?: CLI::prompt('Password (8+ characters)', null, 'required'));
                if (strlen($password) < 8) {
                    CLI::error('Password must be at least 8 characters.');
                    return EXIT_ERROR;
                }
                $id = $model->insert([
                    'username' => $username,
                    'name'     => (string) (CLI::getOption('name') ?: $username),
                    'email'    => CLI::getOption('email') ?: null,
                    'role'     => $role,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'active'   => 1,
                ]);
                CLI::write("User {$username} created (#{$id}).", 'green');
                break;

            case 'password':
            case 'unlock':
                $user = $model->where('username', (string) ($params[1] ?? ''))->first();
                if (! $user) {
                    CLI::error('No such user.');
                    return EXIT_ERROR;
                }
                $data = ['failed_logins' => 0, 'locked_until' => null, 'active' => 1];
                if ($params[0] === 'password') {
                    $password = (string) (CLI::getOption('password') ?: CLI::prompt('New password', null, 'required'));
                    if (strlen($password) < 8) {
                        CLI::error('Password must be at least 8 characters.');
                        return EXIT_ERROR;
                    }
                    $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                $model->update($user['id'], $data);
                CLI::write($params[0] === 'password' ? 'Password changed and account unlocked.' : 'Account unlocked.', 'green');
                break;

            default:
                CLI::error('Use: list, create, password or unlock.');
                return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
