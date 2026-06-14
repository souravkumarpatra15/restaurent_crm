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
        $rid = session('restaurant_id');
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
        return view('admin/menu/form', [
            'pageTitle'  => 'Edit Menu Item',
            'item'       => $this->menuModel->find($id),
            'categories' => $this->menuModel->getCategories(session('restaurant_id')),
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function updateItem($id)
    {
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
        $item = $this->menuModel->find($id);
        $this->menuModel->update($id, ['is_active' => $item['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true]);
    }

    public function deleteItem($id)
    {
        $this->menuModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    public function duplicateItem($id)
    {
        $item = $this->menuModel->find($id);
        unset($item['id'], $item['created_at'], $item['updated_at']);
        $item['name'] = $item['name'] . ' (Copy)';
        $this->menuModel->insert($item);
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
        \Config\Database::connect()->table('menu_categories')->where('id',$id)->update([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'sort_order'  => $this->request->getPost('sort_order') ?? 0,
            'is_active'   => $this->request->getPost('is_active') ?? 1,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    public function toggleCategory($id)
    {
        $db  = \Config\Database::connect();
        $cat = $db->table('menu_categories')->where('id',$id)->get()->getRowArray();
        $db->table('menu_categories')->where('id',$id)->update(['is_active' => $cat['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true]);
    }

    public function deleteCategory($id)
    {
        $db    = \Config\Database::connect();
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
        $db->table('menu_addons')->where('addon_group_id',$id)->delete();
        $db->table('menu_addon_groups')->where('id',$id)->delete();
        return $this->response->setJSON(['success' => true]);
    }

    public function storeAddon()
    {
        \Config\Database::connect()->table('menu_addons')->insert([
            'addon_group_id' => $this->request->getPost('addon_group_id'),
            'name'           => $this->request->getPost('name'),
            'price'          => $this->request->getPost('price') ?? 0,
            'is_active'      => 1,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    public function deleteAddon($id)
    {
        \Config\Database::connect()->table('menu_addons')->where('id',$id)->delete();
        return $this->response->setJSON(['success' => true]);
    }
}
