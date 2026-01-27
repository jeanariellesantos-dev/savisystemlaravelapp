<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Request\RequestController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Request supplies
Route::post('request', [RequestController::class, 'store']);
Route::get('request', [RequestController::class, 'index']);
Route::get('request/pending', [RequestController::class, 'pending']);
Route::get('request/{id}', [RequestController::class, 'show']);
Route::put('request/{id}', [RequestController::class, 'update']);
Route::delete('request/{id}', [RequestController::class, 'destroy']);

require __DIR__ . '/auth.php';