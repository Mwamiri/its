<?php
namespace App\Models;
use CodeIgniter\Model;
class MailLogModel extends Model {
    protected $table = 'mail_log'; protected $allowedFields = ['recipients', 'subject', 'status', 'error']; protected $useTimestamps = false;
}
