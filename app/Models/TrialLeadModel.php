<?php

namespace App\Models;

class TrialLeadModel extends BaseModel
{
    protected $table      = 'trial_leads';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'restaurant_name',
        'phone',
        'email',
        'city',
        'branches',
        'plan_id',
        'message',
        'source',
        'status',
        'notes',
    ];
    protected $useTimestamps = true;
}
