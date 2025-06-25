<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

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

        return $this->response()->success([
            'token' => $token,
            'user'  => new UserResource(
                $user->load('roles')
            ),
        ], 'Login successful');
    }

    public function user(Request $request)
    {
        return $this->response()->success(new UserResource(
             $request->user()->load('roles')
        ), 'Get logged in user');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->response()->success([], 'Logout successful');
    }
}
