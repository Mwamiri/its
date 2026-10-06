<?php
namespace App\Models;

use CodeIgniter\Model;

class MaintenanceModel extends Model
{
    protected $table = 'maintenance_schedules';
    protected $allowedFields = ['client_id', 'asset_id', 'name', 'description', 'interval_days', 'last_run', 'next_run', 'active'];
    protected $useTimestamps = false;
}
