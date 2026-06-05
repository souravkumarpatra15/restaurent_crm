<?php
// application/models/BaseModel.php
namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected $db;
    protected $restaurantId = null;
    protected $branchId = null;

    public function __construct()
    {
        parent::__construct();
        $session = session();
        $this->restaurantId = $session->get('restaurant_id');
        $this->branchId     = $session->get('branch_id');
    }

    protected function tenantScope($builder)
    {
        if ($this->restaurantId) {
            $builder->where($this->table . '.restaurant_id', $this->restaurantId);
        }
        if ($this->branchId && in_array('branch_id', $this->allowedFields)) {
            $builder->where($this->table . '.branch_id', $this->branchId);
        }
        return $builder;
    }

    public function forRestaurant($id)
    {
        $this->restaurantId = $id;
        return $this;
    }

    public function forBranch($id)
    {
        $this->branchId = $id;
        return $this;
    }
}
