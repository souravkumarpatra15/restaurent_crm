<?php
namespace App\Models;

class MenuModel extends BaseModel
{
    protected $table      = 'menu_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'restaurant_id','category_id','name','name_local','description','short_code',
        'sku','barcode','image','item_type','food_type','base_price','tax_percent',
        'tax_type','cess_percent','is_recommended','is_bestseller','is_spicy',
        'calories','prep_time_mins','available_from','available_to','sort_order','is_active'
    ];

    public function getCategoriesWithItems($restaurantId, $branchId = null)
    {
        $cats = $this->db->table('menu_categories')
            ->where('restaurant_id', $restaurantId)
            ->where('is_active', 1)
            ->orderBy('sort_order','ASC')
            ->get()->getResultArray();

        foreach ($cats as &$cat) {
            $items = $this->db->table('menu_items')
                ->where('restaurant_id', $restaurantId)
                ->where('category_id', $cat['id'])
                ->where('is_active', 1)
                ->orderBy('sort_order','ASC')
                ->get()->getResultArray();

            foreach ($items as &$item) {
                $item['variants']     = $this->getVariants($item['id']);
                $item['addon_groups'] = $this->getAddonGroups($item['id']);
            }
            $cat['items'] = $items;
        }
        return $cats;
    }

    public function getItemsWithCategory($restaurantId)
    {
        return $this->db->table('menu_items mi')
            ->select('mi.*, mc.name as category_name')
            ->join('menu_categories mc','mc.id = mi.category_id','left')
            ->where('mi.restaurant_id', $restaurantId)
            ->orderBy('mc.sort_order','ASC')
            ->orderBy('mi.sort_order','ASC')
            ->get()->getResultArray();
    }

    public function getVariants($itemId)
    {
        return $this->db->table('menu_variants')
            ->where('menu_item_id', $itemId)
            ->where('is_active', 1)
            ->orderBy('sort_order','ASC')
            ->get()->getResultArray();
    }

    public function getVariantPrice($variantId)
    {
        $v = $this->db->table('menu_variants')->where('id',$variantId)->get()->getRowArray();
        return $v ? $v['price'] : 0;
    }

    public function getAddonGroups($itemId)
    {
        $groups = $this->db->table('menu_addon_groups g')
            ->select('g.*')
            ->join('menu_item_addon_groups mg','mg.addon_group_id = g.id')
            ->where('mg.menu_item_id', $itemId)
            ->get()->getResultArray();

        foreach ($groups as &$g) {
            $g['addons'] = $this->db->table('menu_addons')
                ->where('addon_group_id', $g['id'])
                ->where('is_active', 1)
                ->get()->getResultArray();
        }
        return $groups;
    }

    public function getAllAddonGroups($restaurantId)
    {
        $groups = $this->db->table('menu_addon_groups')
            ->where('restaurant_id', $restaurantId)
            ->get()->getResultArray();
        foreach ($groups as &$g) {
            $g['addons'] = $this->db->table('menu_addons')
                ->where('addon_group_id', $g['id'])
                ->get()->getResultArray();
        }
        return $groups;
    }

    public function getCategories($restaurantId)
    {
        return $this->db->table('menu_categories mc')
            ->select('mc.*, COUNT(mi.id) as item_count')
            ->join('menu_items mi','mi.category_id = mc.id AND mi.restaurant_id = mc.restaurant_id','left')
            ->where('mc.restaurant_id', $restaurantId)
            ->groupBy('mc.id')
            ->orderBy('mc.sort_order','ASC')
            ->get()->getResultArray();
    }
}
