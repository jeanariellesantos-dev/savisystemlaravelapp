<?php

use App\Http\Controllers\api\Profile\PasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::post('user/register', [UserController::class, 'store']);
Route::post('user/login', [UserController::class, 'login']);

Route::post('user/verify_user_email', [UserController::class, 'verifyUserEmail']);

Route::post('user/resend_email_verification_link', [UserController::class, 'resendEmailVerificationLink']);

Route::middleware(['auth'])->group(function () {
    Route::put('/change_password', [PasswordController::class, 'changeUserPassword']);
    Route::put('user/profile', [UserController::class, 'update']);
    Route::put('user/update_email', [UserController::class, 'updateEmail']);
    Route::post('user/logout', [UserController::class, 'logout']);

});