<?php

namespace App\Models;

use CodeIgniter\Model;

class FormSubmissionModel extends Model
{
    protected $table = 'form_submissions';
    protected $allowedFields = ['form_id', 'submitted_by', 'answers_json', 'submitted_at', 'client_id'];
    protected $useTimestamps = false;
}
