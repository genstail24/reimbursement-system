<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /permissions
     */
    public function index()
    {
        $permissions = Permission::all();

        return $this->response()
            ->success($permissions, 'Permissions retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     * POST /permissions
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $data['name']]);

        return $this->response()
            ->created($permission, 'Permission created successfully.');
    }

    /**
     * Display the specified resource.
     * GET /permissions/{permission}
     */
    public function show(Permission $permission)
    {
        return $this->response()
            ->success($permission, 'Permission details retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     * PUT /permissions/{permission}
     */
    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->name = $data['name'];
        $permission->save();

        return $this->response()
            ->success($permission, 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /permissions/{permission}
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return $this->response()
            ->success([], 'Permission deleted successfully.');
    }
}
