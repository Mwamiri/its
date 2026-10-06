<?php
namespace App\Models;

use CodeIgniter\Model;

class QuoteItemModel extends Model
{
    protected $table = 'quote_items';
    protected $allowedFields = ['quote_id', 'description', 'quantity', 'unit_cost', 'total_cost'];
    protected $useTimestamps = false;
}
