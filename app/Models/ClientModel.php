<?php
namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table = 'clients';
    protected $allowedFields = ['name', 'contact_person', 'phone', 'email', 'address', 'logo_path', 'notes', 'retainer_hours', 'hourly_rate'];
    protected $useTimestamps = false;
}
