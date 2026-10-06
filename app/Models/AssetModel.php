<?php
namespace App\Models;

use CodeIgniter\Model;

class AssetModel extends Model
{
    protected $table = 'assets';
    protected $allowedFields = ['client_id', 'name', 'type', 'brand', 'model', 'serial_number', 'location', 'status', 'notes', 'hostname', 'cpu', 'ram', 'storage', 'os', 'purchase_date', 'warranty_until'];
    protected $useTimestamps = false;
}
