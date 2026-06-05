<?php
namespace App\Models;

class RestaurantModel extends BaseModel
{
    protected $table      = 'restaurants';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'plan_id','name','slug','restaurant_type','cuisine_type','logo','banner',
        'email','phone','whatsapp','website','custom_domain','gst_number','pan_number',
        'fssai_number','address','city','state','country','pincode',
        'currency','currency_symbol','timezone','tax_type','default_tax_percent',
        'service_charge_percent','billing_prefix','billing_counter','kot_prefix','kot_counter',
        'theme_color','receipt_header','receipt_footer','receipt_show_logo',
        'subscription_status','trial_ends_at','subscription_starts_at','subscription_ends_at',
        'billing_cycle','next_billing_date','is_active'
    ];

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[200]',
        'email' => 'required|valid_email|is_unique[restaurants.email,id,{id}]',
        'plan_id' => 'required|integer',
    ];

    public function getWithPlan($id = null)
    {
        $builder = $this->db->table('restaurants r')
            ->select('r.*, p.name as plan_name, p.max_branches, p.max_users, p.max_menu_items')
            ->join('saas_plans p', 'p.id = r.plan_id', 'left');
        if ($id) $builder->where('r.id', $id);
        return $id ? $builder->get()->getRowArray() : $builder->get()->getResultArray();
    }

    public function getStats($restaurantId)
    {
        return [
            'branches'  => $this->db->table('branches')->where('restaurant_id', $restaurantId)->where('is_active',1)->countAllResults(),
            'users'     => $this->db->table('users')->where('restaurant_id', $restaurantId)->where('is_active',1)->countAllResults(),
            'menu_items'=> $this->db->table('menu_items')->where('restaurant_id', $restaurantId)->countAllResults(),
            'orders_today' => $this->db->table('orders')
                ->where('restaurant_id', $restaurantId)
                ->where('DATE(created_at)', date('Y-m-d'))
                ->countAllResults(),
            'revenue_today' => $this->db->table('orders')
                ->selectSum('total_amount')
                ->where('restaurant_id', $restaurantId)
                ->where('DATE(created_at)', date('Y-m-d'))
                ->where('payment_status', 'paid')
                ->get()->getRowArray()['total_amount'] ?? 0,
        ];
    }

    public function generateSlug($name)
    {
        $slug = url_title(strtolower($name), '-', true);
        $original = $slug;
        $count = 1;
        while ($this->where('slug', $slug)->countAllResults()) {
            $slug = $original . '-' . $count++;
        }
        return $slug;
    }

    public function getRestaurantTypeList()
    {
        return [
            'qsr'          => 'QSR (Quick Service Restaurant)',
            'casual_dining'=> 'Casual Dining',
            'fine_dining'  => 'Fine Dining',
            'cafe'         => 'Café / Coffee Shop',
            'bakery'       => 'Bakery & Confectionery',
            'bar'          => 'Bar & Lounge',
            'food_truck'   => 'Food Truck',
            'cloud_kitchen'=> 'Cloud Kitchen',
            'buffet'       => 'Buffet Restaurant',
            'dhaba'        => 'Dhaba',
            'hotel'        => 'Hotel Restaurant',
            'pizza'        => 'Pizza Parlour',
            'ice_cream'    => 'Ice Cream Parlour',
            'juice_bar'    => 'Juice Bar',
            'other'        => 'Other',
        ];
    }
}
