<?php
namespace App\Models;
use CodeIgniter\Model;
class UserModel extends Model {
    protected $table = 'users'; protected $allowedFields = ['username','password','name','email','role','active','client_id','totp_secret','totp_enabled','recovery_codes','failed_logins','locked_until','last_login']; protected $useTimestamps = true; protected $updatedField = "";
}