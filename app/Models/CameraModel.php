<?php

namespace App\Models;

use CodeIgniter\Model;

class CameraModel extends Model
{
    protected $table = 'cameras';
    protected $allowedFields = [
        'client_id',
        'network_device_id',
        'name',
        'camera_type',
        'ip_address',
        'location',
        'recorder',
        'channel_number',
        'rtsp_url_ciphertext',
        'status',
        'notes',
    ];
    protected $useTimestamps = false;
}
