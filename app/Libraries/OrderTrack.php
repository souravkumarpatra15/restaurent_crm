<?php

namespace App\Libraries;

/**
 * Shared step/timeline logic for customer order tracking:
 *  - Public\TrackController (bill-slip QR tracking for takeaway/delivery)
 *  - Public\CustomerMenuController (table-QR dine-in tracking)
 */
class OrderTrack
{
    public static function steps(string $orderType): array
    {
        if ($orderType === 'delivery') {
            return [
                1 => ['name' => 'Order Received',   'icon' => '📋',  'fa' => 'fa-clipboard-list', 'desc' => 'Staff will confirm shortly'],
                2 => ['name' => 'Confirmed',        'icon' => '✅',  'fa' => 'fa-check',          'desc' => 'Kitchen is preparing your food'],
                3 => ['name' => 'In Kitchen',       'icon' => '👨‍🍳', 'fa' => 'fa-fire-burner',    'desc' => 'Your food is being prepared'],
                4 => ['name' => 'Out for Delivery', 'icon' => '🛵',  'fa' => 'fa-motorcycle',     'desc' => 'Your rider is on the way'],
                5 => ['name' => 'Delivered',        'icon' => '🏠',  'fa' => 'fa-house',          'desc' => 'Enjoy your food!'],
            ];
        }

        return [
            1 => ['name' => 'Order Received',   'icon' => '📋',  'fa' => 'fa-clipboard-list', 'desc' => 'Staff will confirm shortly'],
            2 => ['name' => 'Confirmed',        'icon' => '✅',  'fa' => 'fa-check',          'desc' => 'Kitchen is preparing your food'],
            3 => ['name' => 'In Kitchen',       'icon' => '👨‍🍳', 'fa' => 'fa-fire-burner',    'desc' => 'Your food is being prepared'],
            4 => ['name' => 'Ready',            'icon' => '🍽',  'fa' => 'fa-utensils',       'desc' => 'Ready to serve'],
            5 => ['name' => 'Completed',        'icon' => '🎉',  'fa' => 'fa-face-smile',     'desc' => 'Thank you!'],
        ];
    }

    public static function itemProgress(array $items): array
    {
        $total = 0; $prepared = 0;
        foreach ($items as $it) {
            if (in_array($it['status'] ?? '', ['cancelled', 'refund'], true)) continue;
            $total    += (int)$it['quantity'];
            $prepared += in_array($it['status'] ?? '', ['ready', 'served'], true) ? (int)$it['quantity'] : 0;
        }
        return ['total' => $total, 'prepared' => $prepared];
    }
}
