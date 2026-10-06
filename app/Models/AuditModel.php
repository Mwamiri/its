<?php
namespace App\Models;

use CodeIgniter\Model;

class AuditModel extends Model
{
    protected $table = 'audit_log';
    protected $allowedFields = ['user_id', 'action', 'module', 'details', 'ip_address'];
    protected $useTimestamps = false;
}
