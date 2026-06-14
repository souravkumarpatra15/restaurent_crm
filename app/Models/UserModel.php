<?php
namespace App\Models;
class UserModel extends BaseModel
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'restaurant_id','branch_id','role_id','name','email','phone','password',
        'avatar','employee_code','department','salary','join_date','pin',
        'is_active','email_verified_at','last_login_at','last_login_ip'
    ];
    protected $hidden = ['password'];

    public function getWithRole($restaurantId)
    {
        return $this->db->table('users u')
            ->select('u.*, r.name as role_name, b.name as branch_name')
            ->join('roles r','r.id = u.role_id','left')
            ->join('branches b','b.id = u.branch_id','left')
            ->where('u.restaurant_id', $restaurantId)
            ->orderBy('u.name','ASC')
            ->get()->getResultArray();
    }
}
