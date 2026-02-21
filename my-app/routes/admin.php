<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\UnitController;
use App\Http\Controllers\Api\Admin\DealershipController;
use App\Http\Controllers\Api\Admin\RoleController;

Route::middleware('auth:api')->group(function () {

    // =====================================
    // 🔥 ADMIN ONLY ROUTES
    // =====================================
    Route::prefix('admin')
        ->middleware('role:ADMINISTRATOR')
        ->group(function () {
                Route::get('/users', [UserController::class, 'index']);
                Route::post('/users', [UserController::class, 'store']);
                Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus']);
                Route::put('/users/{user}', [UserController::class, 'update']);


                Route::apiResource('categories', CategoryController::class);

                Route::patch('categories/{category}/toggle', 
                    [CategoryController::class, 'toggleStatus']);

                Route::get('products', [ProductController::class, 'index']);
                Route::post('products', [ProductController::class, 'store']);
                Route::get('products/{product}', [ProductController::class, 'show']);
                Route::put('products/{product}', [ProductController::class, 'update']);
                Route::delete('products/{product}', [ProductController::class, 'destroy']);
                Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
                Route::get('/products/{id}/units', [ProductController::class, 'units']);

                Route::get('units', [UnitController::class, 'index']);
                Route::post('units', [UnitController::class, 'store']);
                Route::put('units/{unit}', [UnitController::class, 'update']);
                Route::delete('units/{unit}', [UnitController::class, 'destroy']);
                Route::patch('units/{unit}/toggle', [UnitController::class, 'toggle']);

                Route::get('dealerships', [DealershipController::class, 'index']);
                Route::post('dealerships', [DealershipController::class, 'store']);
                Route::get('dealerships/{id}', [DealershipController::class, 'show']);
                Route::put('dealerships/{id}', [DealershipController::class, 'update']);
                Route::patch('dealerships/{id}/toggle', [DealershipController::class, 'toggleStatus']);
                Route::delete('dealerships/{id}', [DealershipController::class, 'destroy']); // optional

                Route::get('roles', [RoleController::class, 'index']);
                Route::post('roles', [RoleController::class, 'store']);
                Route::get('roles/{id}', [RoleController::class, 'show']);
                Route::put('roles/{id}', [RoleController::class, 'update']);
                Route::patch('roles/{id}/toggle', [RoleController::class, 'toggle']);

                // Optional hard delete
                Route::delete('roles/{id}', [RoleController::class, 'destroy']);

    });

});