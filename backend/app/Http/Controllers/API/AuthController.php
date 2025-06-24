<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            return $this->response()->failed([], 'Unauthorized');
        }

        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        $userData = $user->toArray();
        $userData['roles']       = $user->getRoleNames();
        $userData['permissions'] = $user->getAllPermissions()->pluck('name'); 

        return $this->response()->success([
            'token' => $token,
            'user'  => $userData,
        ], 'Login successful');
    }

    public function user(Request $request)
    {
        $user = $request->user();

        $userData = $user->toArray();
        $userData['roles']       = $user->getRoleNames();
        $userData['permissions'] = $user->getAllPermissions()->pluck('name');

        return $this->response()->success([
            'user' => $userData,
        ], 'Get logged in user');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->response()->success([], 'Logout successful');
    }
}
