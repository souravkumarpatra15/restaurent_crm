<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;
use App\Models\MenuModel;

class MenuController extends BaseController
{
    protected $menuModel;
    public function __construct() { $this->menuModel = new MenuModel(); }

    public function index()
    {
        $rid = session('restaurant_id');
        return view('admin/menu/index', [
            'pageTitle'   => 'Menu Management',
            'activeTab'   => $this->request->getGet('tab') ?? 'items',
            'menuItems'   => $this->menuModel->getItemsWithCategory($rid),
            'categories'  => $this->menuModel->getCategories($rid),
            'addonGroups' => $this->menuModel->getAllAddonGroups($rid),
            'userName'    => session('user_name'),
            'userRole'    => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function createItem()
    {
        return view('admin/menu/form', [
            'pageTitle'  => 'Add Menu Item',
            'item'       => null,
            'categories' => $this->menuModel->getCategories(session('restaurant_id')),
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function storeItem()
    {
        $rid   = session('restaurant_id');
        $count = $this->menuModel->where('restaurant_id',$rid)->countAllResults();
        $check = $this->checkPlanLimit('menu_items',$count);
        if (!$check['allowed']) return redirect()->back()->with('error',$check['message']);
        $data = [
            'restaurant_id' => $rid,
            'category_id'   => $this->request->getPost('category_id'),
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'item_type'     => $this->request->getPost('item_type') ?? 'veg',
            'food_type'     => $this->request->getPost('food_type') ?? 'food',
            'base_price'    => $this->request->getPost('base_price'),
            'tax_percent'   => $this->request->getPost('tax_percent') ?? 0,
            'is_recommended'=> $this->request->getPost('is_recommended') ? 1 : 0,
            'is_bestseller' => $this->request->getPost('is_bestseller') ? 1 : 0,
            'sort_order'    => $this->request->getPost('sort_order') ?? 0,
            'is_active'     => 1,
        ];

        // Handle image upload
        $img = $this->request->getFile('image');
        if ($img && $img->isValid()) {
            $newName = $img->getRandomName();
            $img->move(ROOTPATH . 'public/images/uploads', $newName);
            $data['image'] = $newName;
        }

        $this->menuModel->insert($data);
        return redirect()->to(base_url('admin/menu/items'))->with('success', 'Item added successfully');
    }

    public function editItem($id)
    {
        $item = $this->scopedMenuItem($id);
        if (!$item) {
            return redirect()->to(base_url('admin/menu/items'))->with('error', 'Item not found');
        }
        return view('admin/menu/form', [
            'pageTitle'  => 'Edit Menu Item',
            'item'       => $item,
            'categories' => $this->menuModel->getCategories(session('restaurant_id')),
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function updateItem($id)
    {
        if (!$this->scopedMenuItem($id)) {
            return redirect()->to(base_url('admin/menu/items'))->with('error', 'Item not found');
        }
        $data = [
            'category_id'   => $this->request->getPost('category_id'),
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'item_type'     => $this->request->getPost('item_type'),
            'food_type'     => $this->request->getPost('food_type'),
            'base_price'    => $this->request->getPost('base_price'),
            'tax_percent'   => $this->request->getPost('tax_percent') ?? 0,
            'is_recommended'=> $this->request->getPost('is_recommended') ? 1 : 0,
            'is_bestseller' => $this->request->getPost('is_bestseller') ? 1 : 0,
            'sort_order'    => $this->request->getPost('sort_order') ?? 0,
        ];
        $img = $this->request->getFile('image');
        if ($img && $img->isValid()) {
            $newName = $img->getRandomName();
            $img->move(ROOTPATH . 'public/images/uploads', $newName);
            $data['image'] = $newName;
        }
        $this->menuModel->update($id, $data);
        return redirect()->to(base_url('admin/menu/items'))->with('success', 'Item updated');
    }

    public function toggleItem($id)
    {
        $item = $this->scopedMenuItem($id);
        if (!$item) return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
        $this->menuModel->update($id, ['is_active' => $item['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true]);
    }

    private function scopedMenuItem($id)
    {
        return $this->menuModel->where('id', $id)->where('restaurant_id', session('restaurant_id'))->first();
    }

    public function deleteItem($id)
    {
        $item = $this->scopedMenuItem($id);
        if (!$item) return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
        // Soft-disable instead of a hard delete — hard deletes orphaned past
        // order_items and destroyed revenue history
        $this->menuModel->update($id, ['is_active' => 0]);
        return $this->response->setJSON(['success' => true, 'message' => 'Item deactivated (order history preserved)']);
    }

    public function duplicateItem($id)
    {
        $db      = \Config\Database::connect();
        $orig    = $this->scopedMenuItem($id);
        if (!$orig) {
            return $this->response->setJSON(['success' => false, 'message' => 'Item not found']);
        }

        // Plan limit respected before inserting the copy
        $count = $this->menuModel->where('restaurant_id', session('restaurant_id'))->countAllResults();
        $check = $this->checkPlanLimit('menu_items', $count);
        if (!$check['allowed']) return $this->response->setJSON(['success' => false, 'message' => $check['message']]);

        $db->transStart();

        $item = $orig;
        unset($item['id'], $item['created_at'], $item['updated_at']);
        $item['name'] = $item['name'] . ' (Copy)';
        $newId = $this->menuModel->insert($item, true);

        // Copy variants and addon-group links (they used to be dropped)
        if ($newId) {
            $variants = $db->table('menu_variants')->where('menu_item_id', $id)->get()->getResultArray();
            foreach ($variants as $v) {
                unset($v['id']);
                $v['menu_item_id'] = $newId;
                $db->table('menu_variants')->insert($v);
            }
            $links = $db->table('menu_item_addon_groups')->where('menu_item_id', $id)->get()->getResultArray();
            foreach ($links as $l) {
                $l['menu_item_id'] = $newId;
                $db->table('menu_item_addon_groups')->insert($l);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Could not duplicate item']);
        }
        return $this->response->setJSON(['success' => true]);
    }

    public function storeCategory()
    {
        $db = \Config\Database::connect();
        $db->table('menu_categories')->insert([
            'restaurant_id' => session('restaurant_id'),
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'sort_order'    => $this->request->getPost('sort_order') ?? 0,
            'is_active'     => $this->request->getPost('is_active') ?? 1,
        ]);
        return $this->response->setJSON(['success' => true, 'id' => $db->insertID()]);
    }

    public function updateCategory($id)
    {
        if (!$this->scopedCategory($id)) return $this->response->setJSON(['success'=>false,'message'=>'Category not found']);
        \Config\Database::connect()->table('menu_categories')->where('id',$id)->update([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'sort_order'  => $this->request->getPost('sort_order') ?? 0,
            'is_active'   => $this->request->getPost('is_active') ?? 1,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    private function scopedCategory($id)
    {
        return \Config\Database::connect()->table('menu_categories')->where('id',$id)->where('restaurant_id',session('restaurant_id'))->countAllResults();
    }

    public function toggleCategory($id)
    {
        $db  = \Config\Database::connect();
        if (!$this->scopedCategory($id)) return $this->response->setJSON(['success'=>false,'message'=>'Category not found']);
        $cat = $db->table('menu_categories')->where('id',$id)->get()->getRowArray();
        $db->table('menu_categories')->where('id',$id)->update(['is_active' => $cat['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true]);
    }

    public function deleteCategory($id)
    {
        $db    = \Config\Database::connect();
        if (!$this->scopedCategory($id)) return $this->response->setJSON(['success'=>false,'message'=>'Category not found']);
        $count = $db->table('menu_items')->where('category_id',$id)->countAllResults();
        if ($count > 0) return $this->response->setJSON(['success'=>false,'message'=>'Remove all items first']);
        $db->table('menu_categories')->where('id',$id)->delete();
        return $this->response->setJSON(['success' => true]);
    }

    public function storeAddonGroup()
    {
        $db = \Config\Database::connect();
        $db->table('menu_addon_groups')->insert([
            'restaurant_id'  => session('restaurant_id'),
            'name'           => $this->request->getPost('name'),
            'selection_type' => $this->request->getPost('selection_type') ?? 'multiple',
            'is_required'    => $this->request->getPost('is_required') ?? 0,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    public function deleteAddonGroup($id)
    {
        $db = \Config\Database::connect();
        // Tenant guard — group must belong to this restaurant
        if (!$db->table('menu_addon_groups')->where('id',$id)->where('restaurant_id',session('restaurant_id'))->countAllResults()) {
            return $this->response->setJSON(['success'=>false,'message'=>'Addon group not found']);
        }
        $db->table('menu_addons')->where('addon_group_id',$id)->delete();
        $db->table('menu_item_addon_groups')->where('addon_group_id',$id)->delete();
        $db->table('menu_addon_groups')->where('id',$id)->delete();
        return $this->response->setJSON(['success' => true]);
    }

    public function storeAddon()
    {
        $db = \Config\Database::connect();
        // Parent group must belong to this restaurant
        if (!$db->table('menu_addon_groups')->where('id',$this->request->getPost('addon_group_id'))->where('restaurant_id',session('restaurant_id'))->countAllResults()) {
            return $this->response->setJSON(['success'=>false,'message'=>'Addon group not found']);
        }
        $db->table('menu_addons')->insert([
            'addon_group_id' => $this->request->getPost('addon_group_id'),
            'name'           => $this->request->getPost('name'),
            'price'          => $this->request->getPost('price') ?? 0,
            'is_active'      => 1,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    public function deleteAddon($id)
    {
        $db = \Config\Database::connect();
        // Tenant guard — join through the addon group
        $ok = $db->table('menu_addons a')
            ->join('menu_addon_groups g','g.id = a.addon_group_id')
            ->where('a.id',$id)
            ->where('g.restaurant_id',session('restaurant_id'))
            ->countAllResults();
        if (!$ok) return $this->response->setJSON(['success'=>false,'message'=>'Addon not found']);
        $db->table('menu_addons')->where('id',$id)->delete();
        return $this->response->setJSON(['success' => true]);
    }

    // ── Print Menu Card ───────────────────────────────────
    public function printMenu()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');

        $restaurant = $db->table('restaurants')->where('id', $rid)->get()->getRowArray();
        $branch     = $db->table('branches')->where('id', $bid)->get()->getRowArray();

        // All active categories with their active items + variants
        $categories = $db->table('menu_categories')
            ->where('restaurant_id', $rid)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->get()->getResultArray();

        foreach ($categories as &$cat) {
            $cat['items'] = $db->table('menu_items')
                ->where('restaurant_id', $rid)
                ->where('category_id', $cat['id'])
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->get()->getResultArray();

            // Load variants for each item
            foreach ($cat['items'] as &$item) {
                $item['variants'] = $db->table('menu_variants')
                    ->where('menu_item_id', $item['id'])
                    ->where('is_active', 1)
                    ->orderBy('price', 'ASC')
                    ->get()->getResultArray();
            }
        }

        // Remove empty categories
        $categories = array_values(array_filter($categories, fn($c) => !empty($c['items'])));

        $totalItems = array_sum(array_map(fn($c) => count($c['items']), $categories));

        return view('admin/menu/print_menu', [
            'pageTitle'   => 'Print Menu Card',
            'restaurant'  => $restaurant,
            'branch'      => $branch,
            'categories'  => $categories,
            'totalItems'  => $totalItems,
            'userName'    => session('user_name'),
            'userRole'    => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }
}
