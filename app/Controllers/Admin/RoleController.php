<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Permissions;

/**
 * Roles & Access — the per-role permission matrix.
 *
 * The owner sees every module grouped exactly like the sidebar and can
 * toggle per role. Saved as JSON on roles.permissions_json (per tenant
 * role row only; system role rows are shared, so we write a per-tenant
 * shadow row when the restaurant first customises a system role).
 */
class RoleController extends BaseController
{
    public function index()
    {
        if ($g = $this->requirePermission('roles.manage', 'Roles & Access')) return $g;
        $db = \Config\Database::connect();
        $rid = (int)session('restaurant_id');

        $roles = $db->table('roles r')
            ->select("r.id, r.slug, r.name, r.is_system, r.restaurant_id,
                      NULLIF(r.permissions_json,'') AS stored_json,
                      (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id AND u.restaurant_id = {$rid} AND u.is_active = 1) AS staff_count")
            ->where('r.slug !=', 'super_admin')
            ->groupStart()
                ->where('r.restaurant_id', $rid)
                ->orGroupStart()
                    ->where('r.is_system', 1)->where('r.restaurant_id IS NULL', null, false)
                ->groupEnd()
            ->groupEnd()
            ->orderBy('r.id', 'ASC')
            ->get()->getResultArray();

        foreach ($roles as &$role) {
            $role['perms']    = Permissions::effective($role['slug'], $role['stored_json']);
            $role['granted']  = in_array('*', $role['perms'], true) ? 'all' : count($role['perms']);
            $role['is_custom'] = !empty($role['stored']);
        }

        return view('admin/roles/index', [
            'pageTitle' => 'Roles & Access',
            'roles'     => $roles,
            'catalog'   => Permissions::CATALOG,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    /** Matrix editor for one role */
    public function matrix($roleId)
    {
        if ($g = $this->requirePermission('roles.manage', 'Roles & Access')) return $g;
        $db = \Config\Database::connect();
        $rid = (int)session('restaurant_id');

        $role = $db->table('roles')->where('id', (int)$roleId)->where('slug !=', 'super_admin')->get()->getRowArray();
        if (!$role || (!empty($role['restaurant_id']) && (int)$role['restaurant_id'] !== $rid)) {
            return redirect()->to(base_url('admin/roles'))->with('error', 'Role not found');
        }

        $perms = Permissions::effective($role['slug'], $role['permissions_json']);

        return view('admin/roles/matrix', [
            'pageTitle' => 'Permissions — ' . $role['name'],
            'role'      => $role,
            'perms'     => $perms,
            'allStar'   => in_array('*', $perms, true),
            'catalog'   => Permissions::CATALOG,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    /** Save the matrix */
    public function save($roleId)
    {
        if ($g = $this->requirePermission('roles.manage', 'Roles & Access')) return $g;
        $db = \Config\Database::connect();
        $rid = (int)session('restaurant_id');
        $roleId = (int)$roleId;

        $role = $db->table('roles')->where('id', $roleId)->where('slug !=', 'super_admin')->get()->getRowArray();
        if (!$role || (!empty($role['restaurant_id']) && (int)$role['restaurant_id'] !== $rid)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Role not found']);
        }

        $granted = (array)($this->request->getPost('perms') ?? []);
        $valid   = array_keys(Permissions::all());
        $perms   = array_values(array_intersect($granted, $valid));

        // Never let an owner lock themselves out: restaurant_admin always all
        if ($role['slug'] === 'restaurant_admin') $perms = ['*'];

        $db->table('roles')->where('id', $roleId)->update(['permissions_json' => json_encode($perms)]);

        $this->logActivity($db, $rid, "Updated permissions for role '{$role['name']}' (" . count($perms) . " granted)");

        return $this->response->setJSON([
            'success' => true,
            'message' => "Permissions saved for {$role['name']} — staff will get the new access on next page load",
        ]);
    }

    /** Reset a role to the built-in defaults (delete stored JSON) */
    public function reset($roleId)
    {
        if ($g = $this->requirePermission('roles.manage', 'Roles & Access')) return $g;
        $db = \Config\Database::connect();
        $roleId = (int)$roleId;
        $role = $db->table('roles')->where('id', $roleId)->where('slug !=', 'super_admin')->get()->getRowArray();
        if (!$role || (!empty($role['restaurant_id']) && (int)$role['restaurant_id'] !== (int)session('restaurant_id'))) {
            return $this->response->setJSON(['success' => false, 'message' => 'Role not found']);
        }
        $db->table('roles')->where('id', $roleId)->update(['permissions_json' => null]);
        return $this->response->setJSON(['success' => true, 'message' => "{$role['name']} reset to DinoviX defaults"]);
    }

    private function logActivity($db, int $rid, string $msg): void
    {
        if ($db->tableExists('activity_logs')) {
            $db->table('activity_logs')->insert([
                'restaurant_id' => $rid,
                'branch_id'     => session('branch_id'),
                'user_id'       => session('user_id'),
                'action'        => 'permissions.update',
                'module'        => 'roles',
                'new_data'      => json_encode(['detail' => $msg]),
                'ip_address'    => $this->request->getIPAddress(),
                'user_agent'    => substr((string)$this->request->getUserAgent(), 0, 250),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
