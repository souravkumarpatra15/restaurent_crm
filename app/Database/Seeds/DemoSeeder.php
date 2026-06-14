<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Insert demo restaurant
        $db->table('restaurants')->insert([
            'plan_id'             => 2, // Growth plan
            'name'                => 'Spice Garden Restaurant',
            'slug'                => 'spice-garden',
            'restaurant_type'     => 'casual_dining',
            'cuisine_type'        => 'Indian, Chinese',
            'email'               => 'owner@spicegarden.com',
            'phone'               => '9876543210',
            'address'             => '123 MG Road',
            'city'                => 'Kolkata',
            'state'               => 'West Bengal',
            'country'             => 'India',
            'pincode'             => '700001',
            'gst_number'          => '19AABCU9603R1ZX',
            'currency'            => 'INR',
            'currency_symbol'     => '₹',
            'timezone'            => 'Asia/Kolkata',
            'tax_type'            => 'exclusive',
            'default_tax_percent' => 5.00,
            'billing_prefix'      => 'SG',
            'billing_counter'     => 1,
            'kot_prefix'          => 'KOT',
            'kot_counter'         => 1,
            'theme_color'         => '#FF6B35',
            'receipt_header'      => "Welcome to Spice Garden\nTaste the Difference",
            'receipt_footer'      => "Thank you for dining with us!\nVisit again soon :)",
            'subscription_status' => 'active',
            'trial_ends_at'       => date('Y-m-d H:i:s', strtotime('+30 days')),
            'subscription_ends_at'=> date('Y-m-d H:i:s', strtotime('+1 year')),
            'billing_cycle'       => 'yearly',
            'is_active'           => 1,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
        $restaurantId = $db->insertID();

        // 2. Insert branch
        $db->table('branches')->insert([
            'restaurant_id'  => $restaurantId,
            'name'           => 'Main Branch',
            'code'           => 'SG-MAIN',
            'branch_type'    => 'main',
            'phone'          => '9876543210',
            'email'          => 'main@spicegarden.com',
            'address'        => '123 MG Road',
            'city'           => 'Kolkata',
            'state'          => 'West Bengal',
            'pincode'        => '700001',
            'opening_time'   => '09:00:00',
            'closing_time'   => '23:00:00',
            'has_dine_in'    => 1,
            'has_takeaway'   => 1,
            'has_delivery'   => 1,
            'printer_ip'     => '',
            'printer_port'   => 9100,
            'printer_type'   => 'thermal_80mm',
            'billing_prefix' => 'SG',
            'billing_counter'=> 1,
            'kot_counter'    => 1,
            'is_active'      => 1,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $branchId = $db->insertID();

        // 3. Table areas
        $db->table('table_areas')->insert(['branch_id'=>$branchId,'name'=>'Ground Floor','sort_order'=>1,'is_active'=>1]);
        $areaId = $db->insertID();
        $db->table('table_areas')->insert(['branch_id'=>$branchId,'name'=>'First Floor','sort_order'=>2,'is_active'=>1]);
        $area2Id = $db->insertID();

        // 4. Tables
        for ($i = 1; $i <= 8; $i++) {
            $db->table('tables')->insert([
                'branch_id'    => $branchId,
                'area_id'      => $i <= 5 ? $areaId : $area2Id,
                'table_number' => 'T' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'capacity'     => in_array($i,[1,3,5]) ? 2 : 4,
                'shape'        => $i % 2 === 0 ? 'round' : 'square',
                'status'       => 'available',
                'sort_order'   => $i,
                'is_active'    => 1,
            ]);
        }

        // 5. Menu categories
        $cats = [
            ['name'=>'Starters',    'sort_order'=>1],
            ['name'=>'Main Course', 'sort_order'=>2],
            ['name'=>'Biryani',     'sort_order'=>3],
            ['name'=>'Breads',      'sort_order'=>4],
            ['name'=>'Beverages',   'sort_order'=>5],
            ['name'=>'Desserts',    'sort_order'=>6],
        ];
        $catIds = [];
        foreach ($cats as $cat) {
            $db->table('menu_categories')->insert([
                'restaurant_id' => $restaurantId,
                'name'          => $cat['name'],
                'sort_order'    => $cat['sort_order'],
                'is_active'     => 1,
            ]);
            $catIds[$cat['name']] = $db->insertID();
        }

        // 6. Menu items
        $items = [
            // Starters
            ['category'=>'Starters','name'=>'Paneer Tikka',         'price'=>220,'type'=>'veg',    'tax'=>5,'bestseller'=>1,'recommended'=>1],
            ['category'=>'Starters','name'=>'Chicken Tikka',        'price'=>280,'type'=>'non_veg','tax'=>5,'bestseller'=>1,'recommended'=>0],
            ['category'=>'Starters','name'=>'Veg Spring Roll',      'price'=>160,'type'=>'veg',    'tax'=>5,'bestseller'=>0,'recommended'=>0],
            ['category'=>'Starters','name'=>'Fish Fry',             'price'=>320,'type'=>'non_veg','tax'=>5,'bestseller'=>0,'recommended'=>1],
            // Main Course
            ['category'=>'Main Course','name'=>'Dal Makhani',       'price'=>180,'type'=>'veg',    'tax'=>5,'bestseller'=>1,'recommended'=>1],
            ['category'=>'Main Course','name'=>'Butter Chicken',    'price'=>320,'type'=>'non_veg','tax'=>5,'bestseller'=>1,'recommended'=>1],
            ['category'=>'Main Course','name'=>'Paneer Butter Masala','price'=>260,'type'=>'veg',  'tax'=>5,'bestseller'=>0,'recommended'=>0],
            ['category'=>'Main Course','name'=>'Mutton Rogan Josh', 'price'=>380,'type'=>'non_veg','tax'=>5,'bestseller'=>0,'recommended'=>0],
            // Biryani
            ['category'=>'Biryani','name'=>'Veg Biryani',           'price'=>200,'type'=>'veg',    'tax'=>5,'bestseller'=>0,'recommended'=>0],
            ['category'=>'Biryani','name'=>'Chicken Biryani',       'price'=>280,'type'=>'non_veg','tax'=>5,'bestseller'=>1,'recommended'=>1],
            ['category'=>'Biryani','name'=>'Mutton Biryani',        'price'=>360,'type'=>'non_veg','tax'=>5,'bestseller'=>0,'recommended'=>0],
            // Breads
            ['category'=>'Breads','name'=>'Butter Naan',            'price'=>40, 'type'=>'veg',    'tax'=>5,'bestseller'=>1,'recommended'=>0],
            ['category'=>'Breads','name'=>'Garlic Naan',            'price'=>50, 'type'=>'veg',    'tax'=>5,'bestseller'=>0,'recommended'=>0],
            ['category'=>'Breads','name'=>'Lachha Paratha',         'price'=>45, 'type'=>'veg',    'tax'=>5,'bestseller'=>0,'recommended'=>0],
            // Beverages
            ['category'=>'Beverages','name'=>'Mango Lassi',         'price'=>80, 'type'=>'veg',    'tax'=>0,'bestseller'=>1,'recommended'=>1],
            ['category'=>'Beverages','name'=>'Cold Coffee',         'price'=>100,'type'=>'veg',    'tax'=>0,'bestseller'=>0,'recommended'=>0],
            ['category'=>'Beverages','name'=>'Fresh Lime Soda',     'price'=>60, 'type'=>'veg',    'tax'=>0,'bestseller'=>0,'recommended'=>0],
            ['category'=>'Beverages','name'=>'Masala Chai',         'price'=>40, 'type'=>'veg',    'tax'=>0,'bestseller'=>0,'recommended'=>0],
            // Desserts
            ['category'=>'Desserts','name'=>'Gulab Jamun',          'price'=>80, 'type'=>'veg',    'tax'=>0,'bestseller'=>1,'recommended'=>0],
            ['category'=>'Desserts','name'=>'Kulfi',                'price'=>100,'type'=>'veg',    'tax'=>0,'bestseller'=>0,'recommended'=>1],
        ];

        foreach ($items as $i => $item) {
            $db->table('menu_items')->insert([
                'restaurant_id'  => $restaurantId,
                'category_id'    => $catIds[$item['category']],
                'name'           => $item['name'],
                'item_type'      => $item['type'],
                'food_type'      => in_array($item['category'],['Beverages']) ? 'beverage' : (in_array($item['category'],['Desserts']) ? 'dessert' : 'food'),
                'base_price'     => $item['price'],
                'tax_percent'    => $item['tax'],
                'tax_type'       => 'exclusive',
                'is_recommended' => $item['recommended'],
                'is_bestseller'  => $item['bestseller'],
                'sort_order'     => $i + 1,
                'is_active'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        // 7. Users
        $users = [
            ['role_id'=>2, 'name'=>'Ramesh Kumar',   'email'=>'owner@spicegarden.com',   'phone'=>'9876543210','pin'=>'1234','dept'=>'Management','branch'=>null],
            ['role_id'=>3, 'name'=>'Priya Singh',    'email'=>'manager@spicegarden.com',  'phone'=>'9876543211','pin'=>'2345','dept'=>'Operations','branch'=>$branchId],
            ['role_id'=>4, 'name'=>'Suresh Cashier', 'email'=>'cashier@spicegarden.com',  'phone'=>'9876543212','pin'=>'3456','dept'=>'Billing',   'branch'=>$branchId],
            ['role_id'=>5, 'name'=>'Arun Waiter',    'email'=>'waiter@spicegarden.com',   'phone'=>'9876543213','pin'=>'4567','dept'=>'Service',   'branch'=>$branchId],
            ['role_id'=>6, 'name'=>'Chef Mohan',     'email'=>'kitchen@spicegarden.com',  'phone'=>'9876543214','pin'=>'5678','dept'=>'Kitchen',   'branch'=>$branchId],
            ['role_id'=>7, 'name'=>'Lata Accounts',  'email'=>'accounts@spicegarden.com', 'phone'=>'9876543215','pin'=>'6789','dept'=>'Accounts',  'branch'=>$branchId],
        ];

        foreach ($users as $u) {
            $db->table('users')->insert([
                'restaurant_id'     => $restaurantId,
                'branch_id'         => $u['branch'],
                'role_id'           => $u['role_id'],
                'name'              => $u['name'],
                'email'             => $u['email'],
                'phone'             => $u['phone'],
                'password'          => password_hash('admin@123', PASSWORD_BCRYPT),
                'department'        => $u['dept'],
                'pin'               => $u['pin'],
                'is_active'         => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
        }

        // 8. Demo customers
        $customers = [
            ['name'=>'Rahul Sharma',  'phone'=>'9000000001','email'=>'rahul@example.com', 'spent'=>2450, 'orders'=>8,  'points'=>245],
            ['name'=>'Priti Das',     'phone'=>'9000000002','email'=>'priti@example.com',  'spent'=>1800, 'orders'=>5,  'points'=>180],
            ['name'=>'Amit Roy',      'phone'=>'9000000003','email'=>'amit@example.com',   'spent'=>5600, 'orders'=>18, 'points'=>560],
            ['name'=>'Sonia Gupta',   'phone'=>'9000000004','email'=>'sonia@example.com',  'spent'=>920,  'orders'=>3,  'points'=>92],
            ['name'=>'Rajan Mehta',   'phone'=>'9000000005','email'=>'',                   'spent'=>3200, 'orders'=>11, 'points'=>320],
        ];
        foreach ($customers as $c) {
            $db->table('customers')->insert([
                'restaurant_id' => $restaurantId,
                'name'          => $c['name'],
                'phone'         => $c['phone'],
                'email'         => $c['email'],
                'loyalty_points'=> $c['points'],
                'total_orders'  => $c['orders'],
                'total_spent'   => $c['spent'],
                'last_visit'    => date('Y-m-d H:i:s', strtotime('-' . rand(1,30) . ' days')),
                'source'        => 'walk_in',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        // 9. Loyalty rules
        $db->table('loyalty_rules')->insert([
            'restaurant_id'           => $restaurantId,
            'earn_points_per_amount'  => 1,
            'earn_amount'             => 10,
            'redeem_points_per_amount'=> 1,
            'redeem_value'            => 0.50,
            'min_redeem_points'       => 50,
            'max_redeem_percent'      => 20,
            'is_active'               => 1,
        ]);

        echo "✅ Demo data seeded!\n";
        echo "Restaurant: Spice Garden Restaurant\n";
        echo "Branch: Main Branch (ID: $branchId)\n";
        echo "Tables: 8 (T01–T08)\n";
        echo "Menu Items: " . count($items) . "\n";
        echo "Staff accounts: " . count($users) . "\n";
    }
}
