<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Request\RequestController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Request supplies
Route::post('request', [RequestController::class, 'store']);

require __DIR__ . '/auth.php';