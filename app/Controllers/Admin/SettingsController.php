<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class SettingsController extends BaseController
{
    public function index()
    {
        return view('admin/settings/super_settings', [
            'pageTitle' => 'System Settings',
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    public function save()    { return redirect()->back()->with('success','Settings saved'); }
    public function activityLog()
    {
        $db   = \Config\Database::connect();
        $logs = $db->table('activity_logs al')
            ->select('al.*, u.name as user_name')
            ->join('users u','u.id = al.user_id','left')
            ->orderBy('al.created_at','DESC')
            ->limit(100)
            ->get()->getResultArray();

        return view('admin/settings/activity_log', [
            'pageTitle' => 'Activity Log',
            'logs'      => $logs,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }
}
