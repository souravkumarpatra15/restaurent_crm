<?php

namespace App\Models;

use CodeIgniter\Model;

class TableAreaModel extends Model
{
    protected $table      = 'table_areas';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'branch_id',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $useTimestamps = false;
}