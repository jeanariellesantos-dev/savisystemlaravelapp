<?php



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Request\ApprovalController;
use App\Http\Controllers\Api\Request\FulfillmentController;
use App\Http\Controllers\Api\Request\RequestController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DealershipController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:api')->group(function () {

    // Request supplies
    Route::post('request', [RequestController::class, 'store']);
    Route::get('request', [RequestController::class, 'index']);
    Route::get('request/pending', [RequestController::class, 'pending']);
    Route::get('request/{id}', [RequestController::class, 'show']);
    Route::put('request/{id}', [RequestController::class, 'update']);
    Route::delete('request/{id}', [RequestController::class, 'destroy']);

    //Approval supplies
    Route::post('/request/{id}/approve', [ApprovalController::class, 'approve']);
    Route::post('/request/{id}/fulfill', [FulfillmentController::class, 'fulfill']);
    Route::post('/request/{id}/receive', [FulfillmentController::class, 'receive']);

    //Dealerships
    Route::get('/dealerships', [DealershipController::class, 'index']);
    Route::post('/dealerships', [DealershipController::class, 'store']);
    Route::get('/dealerships/{id}', [DealershipController::class, 'show']);
    Route::put('/dealerships/{id}', [DealershipController::class, 'update']);
    Route::patch('/dealerships/{id}/deactivate', [DealershipController::class, 'deactivate']);
    Route::delete('/dealerships/{id}', [DealershipController::class, 'destroy']);

    //Roles
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    //Product and Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}/units', [ProductController::class, 'units']);

    });

//Shipment
Route::prefix('shipment')->group(function () {
    Route::get('/', [ShipmentController::class, 'index']);          // list
    Route::post('/', [ShipmentController::class, 'store']);         // create
    Route::patch('{id}/status', [ShipmentController::class, 'updateStatus']);
    Route::delete('{id}', [ShipmentController::class, 'destroy']);
});

require __DIR__ . '/auth.php';