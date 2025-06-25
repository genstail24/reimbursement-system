<?php

use App\Http\Controllers\API\ActivityLogController;
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
    // /auth
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    });
});
Route::middleware('auth:sanctum')->group(function () {
    // /users
    Route::prefix('users')->group(function () {
        Route::middleware('permission:user.view')->get('/', [UserController::class, 'index']);
        Route::middleware('permission:user.view')->get('{user}', [UserController::class, 'show']);
        Route::middleware('permission:user.create')->post('/', [UserController::class, 'store']);
        Route::middleware('permission:user.update')->put('{user}', [UserController::class, 'update']);
        Route::middleware('permission:user.delete')->delete('{user}', [UserController::class, 'destroy']);

        Route::middleware('permission:user.assign_role')->group(function () {
            Route::get('{user}/roles', [UserRoleController::class, 'index']);
            Route::put('{user}/roles', [UserRoleController::class, 'sync']);
        });
    });

    // /categories
    Route::prefix('categories')->group(function () {
        Route::middleware('permission:category.view|reimbursement.create|reimbursement.view')->get('/', [CategoryController::class, 'index']);
        Route::middleware('permission:category.view|reimbursement.create|reimbursement.view')->get('{category}', [CategoryController::class, 'show']);
        Route::middleware('permission:category.create')->post('/', [CategoryController::class, 'store']);
        Route::middleware('permission:category.update')->put('{category}', [CategoryController::class, 'update']);
        Route::middleware('permission:category.delete')->delete('{category}', [CategoryController::class, 'destroy']);
    });

    // /reimbursements
    Route::prefix('reimbursements')->group(function () {
        Route::middleware('permission:reimbursement.view')->get('/', [ReimbursementController::class, 'index']);
        Route::middleware('permission:reimbursement.view')->get('metrics', [ReimbursementController::class, 'metrics']);
        Route::middleware('permission:reimbursement.view')->get('{reimbursement}', [ReimbursementController::class, 'show']);
        Route::middleware('permission:reimbursement.delete')->delete('{reimbursement}', [ReimbursementController::class, 'destroy']);
        Route::middleware('permission:reimbursement.create')->post('submission', [ReimbursementController::class, 'submission']);
        Route::middleware('permission:reimbursement.approve')->put('{reimbursement}/approval', [ReimbursementController::class, 'approval']);
    });

    // /roles
    Route::prefix('roles')->group(function () {
        Route::middleware('permission:role.view')->get('/', [RoleController::class, 'index']);
        Route::middleware('permission:role.view')->get('{role}', [RoleController::class, 'show']);
        Route::middleware('permission:role.create')->post('/', [RoleController::class, 'store']);
        Route::middleware('permission:role.update')->put('{role}', [RoleController::class, 'update']);
        Route::middleware('permission:role.delete')->delete('{role}', [RoleController::class, 'destroy']);

        Route::middleware('permission:role.sync_permissions')->group(function () {
            Route::get('{role}/permissions', [RolePermissionController::class, 'index']);
            Route::put('{role}/permissions', [RolePermissionController::class, 'sync']);
        });
    });

    // /permissions
    Route::prefix('permissions')->group(function () {
        Route::middleware('permission:permission.view')->get('/', [PermissionController::class, 'index']);
        Route::middleware('permission:permission.view')->get('{permission}', [PermissionController::class, 'show']);
        Route::middleware('permission:permission.create')->post('/', [PermissionController::class, 'store']);
        Route::middleware('permission:permission.update')->put('{permission}', [PermissionController::class, 'update']);
        Route::middleware('permission:permission.delete')->delete('{permission}', [PermissionController::class, 'destroy']);
    });

    // /log-activities
    Route::prefix('activity-logs')->group(function () {
        Route::middleware('permission:activity-log.view')->get('/', [ActivityLogController::class, 'index']);
        Route::middleware('permission:activity-log.view')->get('{id}', [ActivityLogController::class, 'show']);
    });

});
