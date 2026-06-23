<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class NotificationController extends BaseController
{
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
            ->update(['is_read'=>1,'read_at'=>date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success'=>true]);
    }
}
