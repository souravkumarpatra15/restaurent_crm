<?php
// application/libraries/AuthFilter.php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('user_id')) {
            return redirect()->to(base_url('login'))->with('error', 'Please login to continue');
        }

        if ($arguments) {
            $userRole = $session->get('role_slug');
            if (!in_array($userRole, $arguments) && $userRole !== 'super_admin') {
                return redirect()->back()->with('error', 'Access denied');
            }
        }

        // Check subscription for restaurant users
        if ($session->get('restaurant_id')) {
            $restaurant = \Config\Database::connect()
                ->table('restaurants')
                ->where('id', $session->get('restaurant_id'))
                ->get()->getRowArray();

            if ($restaurant && in_array($restaurant['subscription_status'], ['suspended','cancelled','expired'])) {
                $session->destroy();
                return redirect()->to(base_url('login'))->with('error', 'Your subscription has expired. Please contact support.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
