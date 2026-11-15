<?php

namespace App\Libraries;

/**
 * DinoviX permission catalog — one source of truth for role-wise access.
 *
 * A role's permissions live in roles.permissions_json (JSON array of keys).
 * When that column is NULL the role falls back to its DEFAULTS below, so
 * existing restaurants keep exactly the behaviour they had before the
 * matrix shipped. The owner edits the matrix in Admin → Roles & Access.
 *
 * Keys are module.action strings. UI hints (sidebar) and server guards
 * both read from here — never hardcode a permission key twice.
 */
class Permissions
{
    /** Ordered catalog for the matrix UI: group => [key => label] */
    public const CATALOG = [
        'Point of Sale' => [
            'pos.use'          => 'Open POS & take orders',
            'pos.discount'     => 'Apply discounts & coupons',
            'pos.split_bill'   => 'Split payments',
            'pos.cancel_order' => 'Cancel orders',
        ],
        'Billing & Cash' => [
            'billing.checkout' => 'Collect payments / bill orders',
            'billing.day_close' => 'Cash counter, day close & Z-report',
            'upi.verify'       => 'Verify / reject UPI QR payments',
        ],
        'Kitchen & Delivery' => [
            'kitchen.view'   => 'Kitchen display (KDS)',
            'delivery.manage' => 'Riders & delivery board',
        ],
        'Menu & Tables' => [
            'menu.manage'    => 'Menu items & categories',
            'tables.manage'  => 'Tables & QR codes',
            'reservations.manage' => 'Reservations & table booking',
        ],
        'People' => [
            'staff.manage'  => 'Staff management (add / edit / disable)',
            'roles.manage'  => 'Roles & permission matrix',
            'customers.manage' => 'Customer database & loyalty',
        ],
        'Money & Stock' => [
            'expenses.manage'  => 'Expenses & waste logs',
            'inventory.manage' => 'Inventory & requisitions',
            'coupons.manage'   => 'Coupon campaigns',
        ],
        'Insight' => [
            'orders.view'      => 'Order history & live orders',
            'reports.view'     => 'Reports & dashboards',
            'reports.advanced' => 'Advanced reports & insights',
            'feedback.view'    => 'Customer feedback',
        ],
        'Configuration' => [
            'settings.manage' => 'Restaurant & billing settings',
            'branches.manage' => 'Branches & outlets',
        ],
    ];

    /** Flat key => label map */
    public static function all(): array
    {
        $out = [];
        foreach (self::CATALOG as $keys) {
            foreach ($keys as $k => $v) $out[$k] = $v;
        }
        return $out;
    }

    /** Baseline permissions per system role slug (used when permissions_json is NULL) */
    public const DEFAULTS = [
        'super_admin'      => ['*'],
        'restaurant_admin' => ['*'],
        'branch_manager'   => [
            'pos.use', 'pos.discount', 'pos.split_bill', 'pos.cancel_order',
            'billing.checkout', 'billing.day_close', 'upi.verify',
            'kitchen.view', 'delivery.manage',
            'menu.manage', 'tables.manage', 'reservations.manage',
            'staff.manage', 'customers.manage', 'branches.manage',
            'expenses.manage', 'inventory.manage', 'coupons.manage',
            'orders.view', 'reports.view', 'reports.advanced', 'feedback.view',
        ],
        'cashier'          => [
            'pos.use', 'pos.discount', 'pos.split_bill',
            'billing.checkout', 'billing.day_close', 'upi.verify',
            'orders.view', 'customers.manage', 'delivery.manage',
        ],
        'waiter'           => [
            'pos.use', 'orders.view', 'reservations.manage',
        ],
        'kitchen'          => ['kitchen.view'],
        'accountant'       => [
            'orders.view', 'reports.view', 'reports.advanced',
            'expenses.manage', 'inventory.manage', 'billing.day_close',
        ],
        'delivery'         => ['delivery.manage'],
    ];

    /** Effective permission set for a role slug + optional stored JSON */
    public static function effective(string $slug, ?string $json): array
    {
        if ($slug === 'super_admin') return ['*'];
        $stored = null;
        if ($json !== null && $json !== '') {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) $stored = $decoded;
        }
        $perms = $stored ?? (self::DEFAULTS[$slug] ?? []);
        if (in_array('*', $perms, true)) return ['*'];
        return $perms;
    }

    /** Is $perm granted? ('*' grants everything; unknown keys are denied) */
    public static function allows(array $perms, string $perm): bool
    {
        if (in_array('*', $perms, true)) return true;
        return in_array($perm, $perms, true);
    }
}
