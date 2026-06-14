<?php
namespace App\Models;
class CustomerModel extends BaseModel
{
    protected $table      = 'customers';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'restaurant_id','name','phone','email','dob','anniversary','address',
        'city','pincode','gender','notes','loyalty_points','total_orders','total_spent','last_visit','source','is_active'
    ];
    public function search($restaurantId, $query)
    {
        return $this->db->table('customers')
            ->where('restaurant_id', $restaurantId)
            ->groupStart()
                ->like('phone', $query)->orLike('name', $query)->orLike('email', $query)
            ->groupEnd()
            ->get()->getResultArray();
    }
}
