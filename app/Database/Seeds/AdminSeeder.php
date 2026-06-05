<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('users')->insert([
            'restaurant_id'      => null,
            'branch_id'          => null,
            'role_id'            => 1,
            'name'               => 'Super Admin',
            'email'              => 'superadmin@restoCRM.com',
            'phone'              => '9876543210',
            'password'           => password_hash('admin@123', PASSWORD_BCRYPT),
            'employee_code'      => 'SA001',
            'department'         => 'Admin',
            'pin'                => '1234',
            'is_active'          => 1,
            'email_verified_at'  => date('Y-m-d H:i:s'),
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }
}