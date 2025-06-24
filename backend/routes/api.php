<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ReimbursementController;
use App\Http\Controllers\API\UserController;
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
});
