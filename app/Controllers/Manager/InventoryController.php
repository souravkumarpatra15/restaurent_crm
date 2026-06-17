<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class InventoryController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $rid = (int) session('restaurant_id');
        $bid = session('branch_id');

        if (empty($bid)) {
            $branch = $db->table('branches')
                ->where('restaurant_id', $rid)
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            $bid = $branch['id'] ?? 0;
        }

        $bid = (int) $bid;

        $items = $db->table('inventory_items ii')
            ->select('ii.*, ic.name as category_name, COALESCE(s.current_stock,0) as current_stock, COALESCE(s.unit_cost,0) as unit_cost')
            ->join('inventory_categories ic', 'ic.id = ii.category_id', 'left')
            ->join('inventory_stock s', 's.inventory_item_id = ii.id AND s.branch_id = ' . $bid, 'left', false)
            ->where('ii.restaurant_id', $rid)
            ->orderBy('ii.name', 'ASC')
            ->get()
            ->getResultArray();

        $categories = $db->table('inventory_categories')
            ->where('restaurant_id', $rid)
            ->get()
            ->getResultArray();

        return view('admin/inventory/index', [
            'pageTitle'      => 'Inventory',
            'items'          => $items,
            'categories'     => $categories,
            'userName'       => session('user_name'),
            'userRole'       => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function store()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $db->table('inventory_items')->insert([
            'restaurant_id' => $rid,
            'category_id'   => $this->request->getPost('category_id') ?: null,
            'name'          => $this->request->getPost('name'),
            'sku'           => $this->request->getPost('sku'),
            'unit'          => $this->request->getPost('unit') ?? 'pcs',
            'min_stock'     => $this->request->getPost('min_stock') ?? 0,
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(base_url('admin/inventory'))->with('success','Item added');
    }

    public function transaction()
    {
        $db    = \Config\Database::connect();
        $bid   = session('branch_id');
        $itemId= $this->request->getPost('inventory_item_id');
        $type  = $this->request->getPost('transaction_type');
        $qty   = (float)$this->request->getPost('quantity');
        $cost  = (float)$this->request->getPost('unit_cost');

        // Update stock
        $stock = $db->table('inventory_stock')->where('inventory_item_id',$itemId)->where('branch_id',$bid)->get()->getRowArray();
        $currentQty = $stock['current_stock'] ?? 0;
        $newQty = in_array($type,['purchase','transfer_in','adjustment']) ? $currentQty + $qty : max(0, $currentQty - $qty);

        if ($stock) {
            $db->table('inventory_stock')->where('inventory_item_id',$itemId)->where('branch_id',$bid)
               ->update(['current_stock'=>$newQty,'unit_cost'=>$cost ?: $stock['unit_cost'],'updated_at'=>date('Y-m-d H:i:s')]);
        } else {
            $db->table('inventory_stock')->insert(['inventory_item_id'=>$itemId,'branch_id'=>$bid,'current_stock'=>$newQty,'unit_cost'=>$cost]);
        }

        $db->table('inventory_transactions')->insert([
            'inventory_item_id' => $itemId,
            'branch_id'         => $bid,
            'user_id'           => session('user_id'),
            'transaction_type'  => $type,
            'quantity'          => $qty,
            'unit_cost'         => $cost,
            'notes'             => $this->request->getPost('notes'),
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success'=>true,'new_stock'=>$newQty]);
    }
}
