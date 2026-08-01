<?php
namespace App\Controllers\Public;
use App\Controllers\BaseController;

class FeedbackController extends BaseController
{
    public function index($token)
    {
        $db    = \Config\Database::connect();
        $order = $db->table('orders o')
            ->join('restaurants r','r.id=o.restaurant_id')
            ->join('branches b','b.id=o.branch_id','left')
            ->select('o.id,o.order_number,o.restaurant_id,o.branch_id,r.name as rname,r.theme_color,b.name as bname')
            ->where('o.feedback_token',$token)->get()->getRowArray();

        if (!$order) {
            return view('public/feedback_error');
        }
        $existing = $db->table('order_feedback')->where('order_id',$order['id'])->get()->getRowArray();

        return view('public/feedback', ['order'=>$order,'existing'=>$existing,'token'=>$token]);
    }

    public function store($token)
    {
        $db    = \Config\Database::connect();
        $order = $db->table('orders')->where('feedback_token',$token)->get()->getRowArray();
        if (!$order) return $this->response->setJSON(['success'=>false,'message'=>'Invalid link']);

        $existing = $db->table('order_feedback')->where('order_id',$order['id'])->get()->getRowArray();
        if ($existing) return $this->response->setJSON(['success'=>false,'message'=>'Already submitted']);

        $db->table('order_feedback')->insert([
            'order_id'       => $order['id'],
            'restaurant_id'  => $order['restaurant_id'],
            'branch_id'      => $order['branch_id'],
            'food_rating'    => (int)$this->request->getPost('food_rating'),
            'service_rating' => (int)$this->request->getPost('service_rating'),
            'ambience_rating'=> (int)$this->request->getPost('ambience_rating'),
            'overall_rating' => (int)$this->request->getPost('overall_rating'),
            'comment'        => $this->request->getPost('comment'),
            'would_return'   => $this->request->getPost('would_return') ? 1 : 0,
            'customer_name'  => $order['customer_name'] ?? '',
            'customer_phone' => $order['customer_phone'] ?? '',
            'submitted_at'   => date('Y-m-d H:i:s'),
        ]);
        // Update order feedback status
        $db->table('orders')->where('id',$order['id'])->update(['feedback_submitted'=>1]);
        return $this->response->setJSON(['success'=>true]);
    }
}
