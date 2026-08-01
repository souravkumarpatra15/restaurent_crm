<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class SmartInsightsController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');
        $insights = [];

        // 1. Peak hour detection
        $hourData = $db->table('orders')
            ->select('HOUR(created_at) as hr, COUNT(*) as cnt')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->where('status !=','cancelled')
            ->groupBy('HOUR(created_at)')->orderBy('cnt','DESC')
            ->get()->getResultArray();
        $peakHour = $hourData[0] ?? null;
        if ($peakHour) {
            $h = (int)$peakHour['hr'];
            $insights[] = [
                'type'    => 'peak',
                'icon'    => '🔥',
                'title'   => 'Peak Hour Detected',
                'detail'  => sprintf('Your busiest hour is %s–%s with avg %d orders. Ensure full staff coverage.',
                    date('g A', mktime($h,0,0)), date('g A', mktime($h+1,0,0)), $peakHour['cnt']/30),
                'action'  => 'Schedule extra staff for this window',
                'priority'=> 'high',
            ];
        }

        // 2. Slow-moving items (menu items with < 2 orders in 30 days)
        $slowItems = $db->table('order_items oi')
            ->join('orders o','o.id=oi.order_id')
            ->select('oi.name, SUM(oi.quantity) as total_sold')
            ->where('o.restaurant_id',$rid)->where('o.branch_id',$bid)
            ->where('o.created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->where('o.status !=','cancelled')
            ->groupBy('oi.name')->having('total_sold <', 3)->limit(5)
            ->get()->getResultArray();
        if (!empty($slowItems)) {
            $names = implode(', ', array_column($slowItems, 'name'));
            $insights[] = [
                'type'    => 'menu',
                'icon'    => '📉',
                'title'   => 'Slow-Moving Items',
                'detail'  => "These items sold less than 3 times in 30 days: $names",
                'action'  => 'Consider running a combo offer or removing them from the menu',
                'priority'=> 'medium',
            ];
        }

        // 3. Top item — suggest bundle
        $topItem = $db->table('order_items oi')
            ->join('orders o','o.id=oi.order_id')
            ->select('oi.name, SUM(oi.quantity) as total_sold')
            ->where('o.restaurant_id',$rid)
            ->where('o.created_at >=', date('Y-m-d', strtotime('-7 days')))
            ->where('o.status !=','cancelled')
            ->groupBy('oi.name')->orderBy('total_sold','DESC')->limit(1)
            ->get()->getRowArray();
        if ($topItem) {
            $insights[] = [
                'type'    => 'upsell',
                'icon'    => '⭐',
                'title'   => 'Upsell Opportunity',
                'detail'  => "'{$topItem['name']}' is your #1 this week with {$topItem['total_sold']} orders.",
                'action'  => 'Create a combo meal featuring it to increase avg order value',
                'priority'=> 'medium',
            ];
        }

        // 4. High discount alert
        $avgDisc = $db->table('orders')
            ->selectAvg('discount_amount')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
            ->where('discount_amount >',0)
            ->get()->getRowArray()['discount_amount'] ?? 0;
        $avgOrder = $db->table('orders')->selectAvg('total_amount')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
            ->get()->getRowArray()['total_amount'] ?? 1;
        $discPct = $avgOrder > 0 ? ($avgDisc / $avgOrder * 100) : 0;
        if ($discPct > 12) {
            $insights[] = [
                'type'    => 'discount',
                'icon'    => '⚠️',
                'title'   => 'High Discount Rate',
                'detail'  => sprintf('Average discount is %.1f%% of order value this week. This reduces margins.', $discPct),
                'action'  => 'Review your coupon and staff discount policies',
                'priority'=> 'high',
            ];
        }

        // 5. Revenue trend
        $thisWeek = (float)($db->table('orders')->selectSum('total_amount')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
            ->where('status !=','cancelled')->get()->getRowArray()['total_amount'] ?? 0);
        $lastWeek = (float)($db->table('orders')->selectSum('total_amount')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->where('created_at >=', date('Y-m-d', strtotime('-14 days')))
            ->where('created_at <',  date('Y-m-d', strtotime('-7 days')))
            ->where('status !=','cancelled')->get()->getRowArray()['total_amount'] ?? 0);
        if ($lastWeek > 0) {
            $change = (($thisWeek - $lastWeek) / $lastWeek) * 100;
            $insights[] = [
                'type'    => $change >= 0 ? 'growth' : 'decline',
                'icon'    => $change >= 0 ? '📈' : '📉',
                'title'   => $change >= 0 ? 'Revenue Growing' : 'Revenue Declining',
                'detail'  => sprintf('This week: ₹%s vs last week: ₹%s (%+.1f%%)',
                    number_format($thisWeek), number_format($lastWeek), $change),
                'action'  => $change >= 0
                    ? 'Keep it up! Consider expanding your top items.'
                    : 'Check for order cancellations, staff issues, or menu problems.',
                'priority'=> abs($change) > 20 ? 'high' : 'medium',
            ];
        }

        // 6. Customer retention
        $repeatCusts = $db->table('orders')
            ->select('customer_id, COUNT(*) as visits')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->where('customer_id IS NOT NULL','',false)
            ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->groupBy('customer_id')->having('visits >=', 3)
            ->get()->getNumRows();
        if ($repeatCusts > 0) {
            $insights[] = [
                'type'    => 'loyalty',
                'icon'    => '💎',
                'title'   => 'Loyal Customers Identified',
                'detail'  => "$repeatCusts customers visited 3+ times this month.",
                'action'  => 'Reward them with a special offer — loyal customers spend 67% more',
                'priority'=> 'medium',
            ];
        }

        // 7. Low inventory alert — use correct columns from actual schema
        try {
            $lowStock = $db->table('inventory_items ii')
                ->join('inventory_stock s', 's.inventory_item_id = ii.id AND s.branch_id = '.$bid, 'left', false)
                ->select('ii.name, COALESCE(s.current_stock,0) as qty, COALESCE(ii.min_stock,0) as min_stock, ii.unit')
                ->where('ii.restaurant_id', $rid)
                ->having('qty <=', 'min_stock', false)
                ->limit(5)->get()->getResultArray();
            if (!empty($lowStock)) {
                $names = implode(', ', array_column($lowStock,'name'));
                $insights[] = [
                    'type'    => 'inventory',
                    'icon'    => '📦',
                    'title'   => 'Low Stock Alert',
                    'detail'  => "Reorder needed: $names",
                    'action'  => 'Place purchase orders before running out',
                    'priority'=> 'high',
                ];
            }
        } catch (\Exception $e) {
            // Inventory module not set up yet — skip this insight
            log_message('debug', 'SmartInsights inventory check skipped: '.$e->getMessage());
        }

        // Chart data: last 7 days revenue
        $chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $rev = (float)($db->table('orders')->selectSum('total_amount')
                ->where('restaurant_id',$rid)->where('branch_id',$bid)
                ->where('DATE(created_at)',$d)->where('status !=','cancelled')
                ->get()->getRowArray()['total_amount'] ?? 0);
            $chart[] = ['date'=>date('D d', strtotime($d)), 'rev'=>$rev];
        }

        usort($insights, fn($a,$b) => ($a['priority']==='high'?0:1) - ($b['priority']==='high'?0:1));

        return view('admin/smart/index', [
            'pageTitle' => 'Smart Insights',
            'insights'  => $insights,
            'chart'     => $chart,
            'thisWeek'  => $thisWeek,
            'lastWeek'  => $lastWeek,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }
}
