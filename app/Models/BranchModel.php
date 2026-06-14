<?php
namespace App\Models;
class BranchModel extends BaseModel
{
    protected $table      = 'branches';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'restaurant_id','name','code','branch_type','manager_name','phone','email',
        'address','city','state','pincode','latitude','longitude',
        'opening_time','closing_time','is_24_hours','working_days',
        'has_dine_in','has_takeaway','has_delivery',
        'printer_ip','printer_port','printer_type','kot_printer_ip','kot_printer_port',
        'billing_prefix','billing_counter','is_active'
    ];
    public function getByRestaurant($restaurantId)
    {
        return $this->where('restaurant_id', $restaurantId)->orderBy('name','ASC')->findAll();
    }
}
