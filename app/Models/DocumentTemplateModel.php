<?php
namespace App\Models;

use CodeIgniter\Model;

class DocumentTemplateModel extends Model
{
    protected $table = 'document_templates';
    protected $allowedFields = ['template_type', 'name', 'subject', 'body', 'created_at'];
    protected $useTimestamps = false;
}
