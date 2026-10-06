<?php
namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table = 'login_attempts';
    protected $allowedFields = ['username', 'ip_address', 'attempted_at'];
    protected $useTimestamps = false;
}
