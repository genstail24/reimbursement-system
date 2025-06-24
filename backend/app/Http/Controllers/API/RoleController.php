<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /roles
     */
    public function index()
    {
        $roles = Role::all();

        return $this->response()
            ->success($roles, 'Roles retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     * POST /roles
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create(['name' => $data['name']]);

        return $this->response()
            ->created($role, 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     * GET /roles/{role}
     */
    public function show(Role $role)
    {
        return $this->response()
            ->success($role, 'Role details retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /roles/{role}
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->name = $data['name'];
        $role->save();

        return $this->response()
            ->success($role, 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /roles/{role}
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return $this->response()
            ->success([], 'Role deleted successfully.');
    }

    /**
     * Sync permissions for the given role.
     * POST /roles/{role}/permissions
     */
    public function syncPermissions(Request $request, Role $role)
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
