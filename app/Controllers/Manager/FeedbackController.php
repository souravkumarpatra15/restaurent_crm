<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class FeedbackController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');

        $feedback = $db->table('order_feedback f')
            ->join('orders o','o.id=f.order_id')
            ->select('f.*, o.order_number, o.total_amount')
            ->where('f.restaurant_id',$rid)->where('f.branch_id',$bid)
            ->orderBy('f.submitted_at','DESC')->limit(100)
            ->get()->getResultArray();

        $avgRatings = $db->table('order_feedback')
            ->select('AVG(food_rating) as food, AVG(service_rating) as service,
                      AVG(ambience_rating) as ambience, AVG(overall_rating) as overall,
                      COUNT(*) as total, SUM(would_return) as would_return')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->get()->getRowArray();

        // Rating distribution
        $distribution = $db->table('order_feedback')
            ->select('overall_rating as rating, COUNT(*) as cnt')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->groupBy('overall_rating')->orderBy('overall_rating','DESC')
            ->get()->getResultArray();

        // Low rating alert (< 3 stars in last 7 days)
        $lowRatings = $db->table('order_feedback f')
            ->join('orders o','o.id=f.order_id')
            ->select('f.*, o.order_number, o.customer_name, o.customer_phone')
            ->where('f.restaurant_id',$rid)->where('f.branch_id',$bid)
            ->where('f.overall_rating <', 3)
            ->where('f.submitted_at >=', date('Y-m-d', strtotime('-7 days')))
            ->get()->getResultArray();

        return view('admin/feedback/index', [
            'pageTitle'    => 'Customer Feedback',
            'feedback'     => $feedback,
            'avgRatings'   => $avgRatings,
            'distribution' => $distribution,
            'lowRatings'   => $lowRatings,
            'userName'     => session('user_name'),
            'userRole'     => session('role_slug'),
        ]);
    }
}
