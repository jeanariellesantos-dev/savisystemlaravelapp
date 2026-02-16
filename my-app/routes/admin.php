<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\UnitController;
use App\Http\Controllers\Api\Admin\DealershipController;

Route::middleware('auth:api')->group(function () {

    // =====================================
    // 🔥 ADMIN ONLY ROUTES
    // =====================================
    Route::prefix('admin')
        ->middleware('role:ADMINISTRATOR')
        ->group(function () {
                Route::get('/users', [UserController::class, 'index']);
                Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus']);
                Route::put('/users/{id}', [UserController::class, 'updateUser']);

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

    });

});