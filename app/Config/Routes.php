<?php
namespace Config;
use CodeIgniter\Router\RouteCollection;
/** @var RouteCollection $routes */

// ── PUBLIC ──────────────────────────────────────────────────
$routes->get('/',                       'Auth\AuthController::login');
$routes->get('login',                   'Auth\AuthController::login');
$routes->post('login',                  'Auth\AuthController::doLogin');
$routes->get('logout',                  'Auth\AuthController::logout');
$routes->get('forgot-password',         'Auth\AuthController::forgotPassword');
$routes->post('forgot-password',        'Auth\AuthController::doForgotPassword');
$routes->get('reset-password/(:segment)','Auth\AuthController::resetPassword/$1');
$routes->post('reset-password',         'Auth\AuthController::doResetPassword');
$routes->get('back-to-super',           'Auth\AuthController::backToSuper');

// ── SUPER ADMIN ──────────────────────────────────────────────
$routes->group('super', ['filter' => 'auth:super_admin'], function ($routes) {
    $routes->get('dashboard',                       'Admin\DashboardController::index');

    // Restaurants
    $routes->get('restaurants',                     'Admin\RestaurantController::index');
    $routes->get('restaurants/create',              'Admin\RestaurantController::create');
    $routes->post('restaurants/store',              'Admin\RestaurantController::store');
    $routes->get('restaurants/edit/(:num)',         'Admin\RestaurantController::edit/$1');
    $routes->post('restaurants/update/(:num)',      'Admin\RestaurantController::update/$1');
    $routes->get('restaurants/view/(:num)',         'Admin\RestaurantController::view/$1');
    $routes->post('restaurants/toggle/(:num)',      'Admin\RestaurantController::toggle/$1');
    $routes->post('restaurants/delete/(:num)',      'Admin\RestaurantController::delete/$1');
    $routes->post('restaurants/login-as/(:num)',    'Admin\RestaurantController::loginAs/$1');

    // Plans
    $routes->get('plans',                           'Admin\PlanController::index');
    $routes->get('plans/create',                    'Admin\PlanController::create');
    $routes->post('plans/store',                    'Admin\PlanController::store');
    $routes->get('plans/edit/(:num)',               'Admin\PlanController::edit/$1');
    $routes->post('plans/update/(:num)',            'Admin\PlanController::update/$1');

    // Subscriptions
    $routes->get('subscriptions',                   'Admin\SubscriptionController::index');
    $routes->post('subscriptions/change-plan/(:num)', 'Admin\SubscriptionController::changePlan/$1');
    $routes->post('subscriptions/suspend/(:num)',   'Admin\SubscriptionController::suspend/$1');
    $routes->post('subscriptions/activate/(:num)', 'Admin\SubscriptionController::activate/$1');

    // Reports
    $routes->get('reports/revenue',                 'Admin\ReportController::revenue');
    $routes->get('reports/subscriptions',           'Admin\ReportController::subscriptions');

    // Settings
    $routes->get('settings',                        'Admin\SettingsController::index');
    $routes->post('settings/save',                  'Admin\SettingsController::save');
    $routes->get('activity-log',                    'Admin\SettingsController::activityLog');

    // Notifications
    $routes->get('notifications',                   'Admin\NotificationController::index');
    $routes->post('notifications/mark-read',        'Admin\NotificationController::markRead');
    $routes->get('notifications/count',             'Admin\NotificationController::count');
    $routes->post('subscriptions/remind/(:num)',    'Admin\SubscriptionController::remind/$1');

    // Notifications
    $routes->get('notifications',                   'Admin\NotificationController::index');
    $routes->post('notifications/mark-read',        'Admin\NotificationController::markRead');
    $routes->get('notifications/count',             'Admin\NotificationController::count');
});

// ── RESTAURANT ADMIN / MANAGER ───────────────────────────────
$routes->group('admin', ['filter' => 'auth:restaurant_admin,branch_manager,super_admin'], function ($routes) {
    $routes->get('dashboard',                       'Manager\DashboardController::index');
    $routes->get('dashboard/live-orders',           'Manager\DashboardController::liveOrders');
    $routes->get('dashboard/hourly-chart',          'Manager\DashboardController::hourlyChart');
    $routes->get('dashboard/switch-branch',         'Manager\DashboardController::switchBranch');

    // Branches
    $routes->get('branches',                        'Manager\BranchController::index');
    $routes->get('branches/create',                 'Manager\BranchController::create');
    $routes->post('branches/store',                 'Manager\BranchController::store');
    $routes->get('branches/edit/(:num)',            'Manager\BranchController::edit/$1');
    $routes->post('branches/update/(:num)',         'Manager\BranchController::update/$1');
    $routes->post('branches/toggle/(:num)',         'Manager\BranchController::toggle/$1');

    // Users
    $routes->get('users',                           'Manager\UserController::index');
    $routes->get('users/create',                    'Manager\UserController::create');
    $routes->post('users/store',                    'Manager\UserController::store');
    $routes->get('users/edit/(:num)',               'Manager\UserController::edit/$1');
    $routes->post('users/update/(:num)',            'Manager\UserController::update/$1');
    $routes->post('users/toggle/(:num)',            'Manager\UserController::toggle/$1');

    // Menu
    $routes->get('menu/items',                      'Manager\MenuController::index');
    $routes->get('menu/items/create',               'Manager\MenuController::createItem');
    $routes->post('menu/items/store',               'Manager\MenuController::storeItem');
    $routes->get('menu/items/edit/(:num)',          'Manager\MenuController::editItem/$1');
    $routes->post('menu/items/update/(:num)',       'Manager\MenuController::updateItem/$1');
    $routes->post('menu/items/toggle/(:num)',       'Manager\MenuController::toggleItem/$1');
    $routes->post('menu/items/delete/(:num)',       'Manager\MenuController::deleteItem/$1');
    $routes->post('menu/items/duplicate/(:num)',    'Manager\MenuController::duplicateItem/$1');
    $routes->post('menu/categories/store',          'Manager\MenuController::storeCategory');
    $routes->post('menu/categories/update/(:num)', 'Manager\MenuController::updateCategory/$1');
    $routes->post('menu/categories/toggle/(:num)', 'Manager\MenuController::toggleCategory/$1');
    $routes->post('menu/categories/delete/(:num)', 'Manager\MenuController::deleteCategory/$1');
    $routes->post('menu/addon-groups/store',        'Manager\MenuController::storeAddonGroup');
    $routes->post('menu/addon-groups/delete/(:num)','Manager\MenuController::deleteAddonGroup/$1');
    $routes->post('menu/addons/store',              'Manager\MenuController::storeAddon');
    $routes->post('menu/addons/delete/(:num)',      'Manager\MenuController::deleteAddon/$1');

    // Tables
    $routes->get('tables',                          'Manager\TableController::index');
    $routes->post('tables/store',                   'Manager\TableController::store');
    $routes->post('tables/update/(:num)',           'Manager\TableController::update/$1');
    $routes->post('tables/toggle/(:num)',           'Manager\TableController::toggle/$1');

    // Customers
    $routes->get('customers',                       'Manager\CustomerController::index');
    $routes->get('customers/view/(:num)',           'Manager\CustomerController::view/$1');
    $routes->post('customers/store',                'Manager\CustomerController::store');
    $routes->post('customers/update/(:num)',        'Manager\CustomerController::update/$1');

    // Orders
    $routes->get('orders',                          'Manager\OrderController::index');
    $routes->get('orders/view/(:num)',              'Manager\OrderController::view/$1');
    $routes->post('orders/cancel/(:num)',           'Manager\OrderController::cancel/$1');

    // Reports
    $routes->get('reports/sales',                   'Manager\ReportController::sales');
    $routes->get('reports/items',                   'Manager\ReportController::items');
    $routes->get('reports/payments',                'Manager\ReportController::payments');
    $routes->get('reports/expenses',                'Manager\ReportController::expenses');

    // Expenses
    $routes->get('expenses',                        'Manager\ExpenseController::index');
    $routes->post('expenses/store',                 'Manager\ExpenseController::store');
    $routes->post('expenses/approve/(:num)',        'Manager\ExpenseController::approve/$1');

    // Reservations
    $routes->get('reservations',                    'Manager\ReservationController::index');
    $routes->post('reservations/store',             'Manager\ReservationController::store');
    $routes->post('reservations/update-status/(:num)','Manager\ReservationController::updateStatus/$1');

    // Coupons
    $routes->get('coupons',                         'Manager\CouponController::index');
    $routes->post('coupons/store',                  'Manager\CouponController::store');
    $routes->post('coupons/toggle/(:num)',          'Manager\CouponController::toggle/$1');

    // Settings
    $routes->get('settings',                        'Manager\SettingsController::index');
    $routes->post('settings/save',                  'Manager\SettingsController::save');

    // Inventory
    $routes->get('inventory',                       'Manager\InventoryController::index');
    $routes->post('inventory/store',                'Manager\InventoryController::store');
    $routes->post('inventory/transaction',          'Manager\InventoryController::transaction');

    // Notifications
    $routes->get('notifications/count',             'Manager\NotificationController::count');
    $routes->post('notifications/mark-read',        'Manager\NotificationController::markRead');
});

// ── POS ──────────────────────────────────────────────────────
$routes->group('pos', ['filter' => 'auth:cashier,waiter,branch_manager,restaurant_admin'], function ($routes) {
    $routes->get('/',                               'Staff\PosController::index');
    $routes->get('table-map',                       'Staff\PosController::tableMap');
    $routes->get('new-order/(:segment)',            'Staff\PosController::newOrder/$1');
    $routes->get('order/(:num)',                    'Staff\PosController::orderDetail/$1');
    $routes->post('order/create',                   'Staff\PosController::createOrder');
    $routes->post('order/checkout',                 'Staff\PosController::checkout');
    $routes->post('order/print-kot',                'Staff\PosController::printKot');
    $routes->post('order/print-bill',               'Staff\PosController::printBill');
    $routes->post('order/cancel/(:num)',            'Staff\PosController::cancelOrder/$1');
    $routes->post('order/apply-coupon',             'Staff\PosController::applyCoupon');
    $routes->get('active-orders',                   'Staff\PosController::activeOrders');
    $routes->get('table-orders/(:num)',             'Staff\PosController::tableOrders/$1');
    $routes->get('kitchen',                         'Staff\KitchenController::index');
    $routes->post('kitchen/update-status',          'Staff\KitchenController::updateStatus');
    $routes->get('shift/summary',                   'Staff\ShiftController::summary');
});

// Slip print routes (web-based fallback when no thermal printer)
$routes->get('pos/slip/kot/(:num)',  'Staff\PosController::kotSlip/$1',  ['filter'=>'auth:cashier,waiter,branch_manager,restaurant_admin,super_admin']);
$routes->get('pos/slip/bill/(:num)', 'Staff\PosController::billSlip/$1', ['filter'=>'auth:cashier,waiter,branch_manager,restaurant_admin,super_admin']);
