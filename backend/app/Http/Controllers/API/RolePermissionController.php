<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * Display a list of permissions assigned to the given role.
     * GET /roles/{role}/permissions
     */
    public function index(Role $role)
    {
        $permissions = $role->permissions->pluck('name');

        return $this->response()
            ->success($permissions, 'Permissions retrieved for role: ' . $role->name);
    }

    /**
     * Sync permissions for the given role.
     * POST /roles/{role}/permissions
     */
    public function sync(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($data['permissions']);

        return $this->response()
            ->success($role->permissions->pluck('name'), 'Permissions synced for role: ' . $role->name);
    }
}
