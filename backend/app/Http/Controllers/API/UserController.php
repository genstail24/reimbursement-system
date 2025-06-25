<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Helpers\HTTPResponse;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->get();
        return $this->response()->success(UserResource::collection($users), 'List user berhasil diambil');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // $user->assignRole('role'); // kalau nanti pakai Spatie role

            return $this->response()->created(new UserResource($user), 'User berhasil dibuat');
        } catch (\Throwable $e) {
            return $this->response()->error($request, $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {   
        $user->load('roles');

        return $this->response()->success(new UserResource($user), 'Detail user');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name'     => 'sometimes|required|string|max:255',
                'email'    => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => 'nullable|string|min:6',
            ]);

            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }

            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            return $this->response()->success(new UserResource($user), 'User berhasil diperbarui');
        } catch (\Throwable $e) {
            return $this->response()->error($request, $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();
            return $this->response()->success(new UserResource($user), 'User berhasil dihapus');
        } catch (\Throwable $e) {
            return $this->response()->error(request(), $e);
        }
    }
}
