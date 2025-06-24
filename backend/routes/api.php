<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\ReimbursementController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\RolePermissionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\UserRoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which is assigned the "api" middleware group.
*/

Route::get('/hello-world', fn(Request $request) => 'Hello World');

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // /users
    Route::apiResource('users', UserController::class);
    Route::get('users/{user}/roles', [UserRoleController::class, 'index']);
    Route::put('users/{user}/roles', [UserRoleController::class, 'sync']);

    // /categories
    Route::apiResource('categories', CategoryController::class);

    // /reimbursements
    Route::prefix('reimbursements')->group(function () {
        Route::apiResource('/', ReimbursementController::class)
            ->parameters(['' => 'reimbursement'])
            ->only(['index', 'show', 'destroy']);
        Route::post('submission', [ReimbursementController::class, 'submission'])
            ->name('reimbursements.submission');
        Route::put('{reimbursement}/approval', [ReimbursementController::class, 'approval'])
            ->name('reimbursements.approval');
    });

    // /roles
    Route::apiResource('roles', RoleController::class);
    Route::get('roles/{role}/permissions', [RolePermissionController::class, 'index']);
    Route::put('roles/{role}/permissions', [RolePermissionController::class, 'sync']);

    // /permissions
    Route::apiResource('permissions', PermissionController::class);
});
