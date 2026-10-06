<?php
namespace App\Models;
use CodeIgniter\Model;
class TicketUpdateModel extends Model {
    protected $table = 'ticket_updates';
    protected $useTimestamps = false;
    protected $allowedFields = ['ticket_id','user_id','author_name','author_role','visibility','message','created_at'];
}
