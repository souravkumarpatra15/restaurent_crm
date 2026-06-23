<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class NotificationController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $db->table('notifications')
            ->where('user_id', session('user_id'))
            ->where('is_read', 0)
            ->update(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);

        $notes = $db->table('notifications')
            ->where('user_id', session('user_id'))
            ->orderBy('created_at', 'DESC')
            ->limit(50)->get()->getResultArray();

        return view('admin/notifications/index', [
            'pageTitle'     => 'Notifications',
            'notifications' => $notes,
            'userName'      => session('user_name'),
            'userRole'      => session('role_slug'),
        ]);
    }

    public function count()
    {
        $count = \Config\Database::connect()->table('notifications')
            ->where('user_id', session('user_id'))
            ->where('is_read', 0)->countAllResults();
        return $this->response->setJSON(['count' => $count]);
    }

    public function markRead()
    {
        \Config\Database::connect()->table('notifications')
            ->where('user_id', session('user_id'))
            ->update(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true]);
    }
}
