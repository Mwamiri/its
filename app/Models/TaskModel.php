<?php
namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'tasks';
    protected $allowedFields = ['ticket_id', 'item', 'serial_number', 'complaint', 'diagnosis', 'action_taken', 'recommendation', 'status', 'scope', 'parts_cost', 'labor_cost'];
    protected $useTimestamps = false;
}
