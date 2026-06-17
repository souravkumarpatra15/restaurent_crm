<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class ExpenseController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $expenses = $db->table('expenses e')
            ->select('e.*, ec.name as category_name, u.name as staff_name, b.name as branch_name')
            ->join('expense_categories ec','ec.id = e.category_id','left')
            ->join('users u','u.id = e.user_id','left')
            ->join('branches b','b.id = e.branch_id','left')
            ->where('e.restaurant_id', $rid)
            ->where('DATE(e.expense_date) >=', $from)
            ->where('DATE(e.expense_date) <=', $to)
            ->orderBy('e.expense_date','DESC')
            ->get()->getResultArray();

        $categories = $db->table('expense_categories')->where('restaurant_id',$rid)->get()->getResultArray();
        $branches   = $db->table('branches')->where('restaurant_id',$rid)->get()->getResultArray();
        $total      = array_sum(array_column($expenses,'amount'));

        return view('admin/expenses/index', [
            'pageTitle'   => 'Expenses',
            'expenses'    => $expenses,
            'categories'  => $categories,
            'branches'    => $branches,
            'total'       => $total,
            'from'        => $from,
            'to'          => $to,
            'userName'    => session('user_name'),
            'userRole'    => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function store()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');

        // auto-create category if needed
        $catId = $this->request->getPost('category_id');
        $newCat = $this->request->getPost('new_category');
        if (!$catId && $newCat) {
            $db->table('expense_categories')->insert(['restaurant_id'=>$rid,'name'=>$newCat,'is_active'=>1]);
            $catId = $db->insertID();
        }

        $db->table('expenses')->insert([
            'restaurant_id' => $rid,
            'branch_id'     => $bid,
            'category_id'   => $catId ?: null,
            'user_id'       => session('user_id'),
            'title'         => $this->request->getPost('title'),
            'description'   => $this->request->getPost('description'),
            'amount'        => $this->request->getPost('amount'),
            'expense_date'  => $this->request->getPost('expense_date') ?? date('Y-m-d'),
            'payment_method'=> $this->request->getPost('payment_method') ?? 'cash',
            'reference'     => $this->request->getPost('reference'),
            'is_approved'   => session('role_slug') === 'branch_manager' ? 1 : 0,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(base_url('admin/expenses'))->with('success','Expense added');
    }

    public function approve($id)
    {
        \Config\Database::connect()->table('expenses')->where('id',$id)->update([
            'is_approved' => 1,
            'approved_by' => session('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->response->setJSON(['success'=>true]);
    }
}
