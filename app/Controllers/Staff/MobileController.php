<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;

/**
 * Mobile app handshake (DinoviX Android WebView suite).
 *
 * The APK calls this once after login and gets:
 *   - which pocket sections this role needs (bottom-nav tabs)
 *   - the CSS selectors of the web chrome it should hide to feel like
 *     a native app (sidebars, toggles) on pages that still use them
 *
 * Admin-class roles get an empty section list = plain full-website
 * mode, exactly as before.
 */
class MobileController extends BaseController
{
    /** Chrome (web page furniture) to hide per role — data-driven so the APK never hardcodes page structure. */
    private function chromeHide(): array
    {
        return [
            '.sidebar', '.sidebar-overlay', '.sidebar-toggle',
            '.mobile-header', '#notifPanel',
        ];
    }

    public function bootstrap()
    {
        if (!session('user_id')) {
            return $this->response->setStatusCode(401)
                ->setJSON(['auth' => false, 'message' => 'Not logged in']);
        }

        $role = session('role_slug');
        $u    = session('user_name');

        // Which pocket sections does this role get on the bottom bar?
        $sections = match ($role) {
            // The counter + billers
            'cashier', 'waiter' => [
                ['id' => 'pos',    'label' => 'POS',      'icon' => '🪑', 'url' => base_url('pos')],
                ['id' => 'upi',    'label' => 'Payments', 'icon' => '💳', 'url' => base_url('pos/upi/pending')],
            ],
            // The kitchen screen
            'kitchen' => [
                ['id' => 'kitchen', 'label' => 'Kitchen', 'icon' => '🔥', 'url' => base_url('pos/kitchen')],
            ],
            // Delivery staff (riders) — their own runs first, board as the second tab
            'delivery' => [
                ['id' => 'rides',  'label' => 'Deliveries', 'icon' => '🛵', 'url' => base_url('driver')],
                ['id' => 'board',  'label' => 'Board',      'icon' => '🗺️', 'url' => base_url('pos/delivery')],
            ],
            // Managers/admins — full site without a fake app feel
            'restaurant_admin', 'branch_manager', 'super_admin', 'accountant' => [],
            default => [],
        };

        return $this->response->setJSON([
            'auth'   => true,
            'user'   => ['name' => $u, 'role' => $role],
            'sections' => $sections,
            'chrome' => [
                // hide these on every page so web navigation furniture
                // never fights the app shell (harmless when absent)
                'hide_css' => $this->chromeHide(),
            ],
        ]);
    }
}
