<?php

namespace App\Models;

use CodeIgniter\Model;

class VaultCredentialModel extends Model
{
    protected $table = 'vault_credentials';
    protected $allowedFields = [
        'client_id',
        'network_device_id',
        'label',
        'username',
        'secret_ciphertext',
        'notes',
        'created_by',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = false;
}
