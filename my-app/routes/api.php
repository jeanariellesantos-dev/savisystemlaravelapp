<?php


use App\Http\Controllers\Api\Request\ApprovalController;
use App\Http\Controllers\Api\Request\FulfillmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Request\RequestController;
use App\Http\Controllers\Api\ShipmentController;


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

});

//Approval supplies
Route::post('/request/{id}/approve', [ApprovalController::class, 'approve']);
Route::post('/request/{id}/fulfill', [FulfillmentController::class, 'fulfill']);
Route::post('/request/{id}/receive', [FulfillmentController::class, 'receive']);

//Shipment
Route::prefix('shipment')->group(function () {
    Route::get('/', [ShipmentController::class, 'index']);          // list
    Route::post('/', [ShipmentController::class, 'store']);         // create
    Route::patch('{id}/status', [ShipmentController::class, 'updateStatus']);
    Route::delete('{id}', [ShipmentController::class, 'destroy']);
});

require __DIR__ . '/auth.php';