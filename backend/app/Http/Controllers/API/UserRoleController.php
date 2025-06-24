<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserRoleController extends Controller
{
    /**
     * Display the roles assigned to the specified user.
     * GET /users/{user}/roles
     */
    public function index(User $user)
    {
        return $this->response()
            ->success($user->getRoleNames(), 'Roles retrieved for user: ' . $user->name);
    }

    /**
     * Sync the roles for the specified user.
     * POST /users/{user}/roles
     */
    public function sync(Request $request, User $user)
    {
        $data = $request->validate([
            'roles'   => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user->syncRoles($data['roles']);

        return $this->response()
            ->success($user->getRoleNames(), 'Roles synced for user: ' . $user->name);
    }
}
