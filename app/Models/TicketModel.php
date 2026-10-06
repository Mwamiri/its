<?php
namespace App\Models;
use CodeIgniter\Model;
class TicketModel extends Model {
    protected $table = 'tickets'; protected $allowedFields = ['ticket_number','client_id','subject','description','priority','status','visit_date','ticket_type','assigned_to','parent_id','response_due_at','due_at','first_response_at','resolved_at','rating','rating_comment']; protected $useTimestamps = true; protected $updatedField = "";
}