<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('user/register', [UserController::class, 'store']);
Route::post('user/login', [UserController::class, 'login']);

Route::post('user/verify_user_email', [UserController::class, 'verifyUserEmail']);

Route::post('user/resend_email_verification_link', [UserController::class, 'resendEmailVerificationLink']);