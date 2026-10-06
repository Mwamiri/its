<?php
namespace App\Models;

use CodeIgniter\Model;

class TaskPhotoModel extends Model
{
    protected $table = 'task_photos';
    protected $allowedFields = ['task_id', 'photo_path', 'label'];
    protected $useTimestamps = false;
}
