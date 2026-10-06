<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomFormModel extends Model
{
    protected $table = 'custom_forms';
    protected $allowedFields = ['title', 'description', 'fields_json', 'active', 'created_at', 'updated_at'];
    protected $useTimestamps = false;
}
