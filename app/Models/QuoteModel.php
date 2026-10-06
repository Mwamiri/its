<?php
namespace App\Models;

use CodeIgniter\Model;

class QuoteModel extends Model
{
    protected $table = 'quotes';
    protected $allowedFields = ['quote_number', 'ticket_id', 'client_id', 'subject', 'status', 'total', 'notes', 'approved_at', 'approved_by'];
    protected $useTimestamps = false;
}
