<?php
namespace App\Models;

use CodeIgniter\Model;

class SignatureModel extends Model
{
    protected $table = 'signatures';
    protected $allowedFields = ['ticket_id', 'signed_by_name', 'signature_path', 'verification_code', 'ip_address', 'signed_at'];
    protected $useTimestamps = false;
}
