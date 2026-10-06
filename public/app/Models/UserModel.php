<?php
namespace App\Models;
use CodeIgniter\Model;
class UserModel extends Model {
    protected $table = 'users';
    protected $useTimestamps = false;
    protected $allowedFields = ['username','password','name','role','active'];
}