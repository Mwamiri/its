<?php

namespace App\Models;

use CodeIgniter\Model;

class NetworkDeviceModel extends Model
{
    protected $table = 'network_devices';
    protected $allowedFields = [
        'client_id',
        'name',
        'device_type',
        'hostname',
        'ip_address',
        'mac_address',
        'manufacturer',
        'model',
        'location',
        'status',
        'notes',
        'monitor_enabled',
        'monitor_port',
        'monitor_state',
        'last_checked',
        'last_seen',
    ];
    protected $useTimestamps = false;
}
