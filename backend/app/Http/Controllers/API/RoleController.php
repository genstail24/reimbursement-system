<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Resources\RoleResource;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return $this->response()
            ->success(RoleResource::collection($roles), 'Roles retrieved successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create(['name' => $data['name']]);

        return $this->response()
            ->created(new RoleResource($role), 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        return $this->response()
            ->success(new RoleResource($role), 'Role details retrieved successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $data['name']]);

        return $this->response()
            ->success(new RoleResource($role), 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return $this->response()
            ->success([], 'Role deleted successfully.');
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($data['permissions']);

        return $this->response()
            ->success(
                $role->permissions->pluck('name')->all(),
                'Permissions synced for role: ' . $role->name
            );
    }
}
