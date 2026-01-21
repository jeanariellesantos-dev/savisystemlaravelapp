<?php

namespace App\Customs\Services;

use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

class EmailVerificationService
{


    public function generateVerificationLink($email)
    {
        $checkifTokenExists = EmailVerificationToken::where("email", $email)->first();

        if ($checkifTokenExists)
            $checkifTokenExists->delete();

        $token = Str::uuid();

        $url = config('app.url') . "?token=" . $token . "&email=" . $email;
        $saveToken = EmailVerificationToken::create([
            "email" => $email,
            "token" => $token,
            "expired_at" => now()->addMinutes(60),
        ]);

        if ($saveToken) {
            return $url;
        }

    }

    public function sendVerificationLink($user)
    {
        Notification::send($user, new EmailVerificationNotification($this->generateVerificationLink($user->email)));

    }

    /**
     * 
     * Resend Link token
     */
    public function resendLink($email)
    {
        $user = User::where("email", $email)->first();
        if ($user) {
            $this->sendVerificationLink($user);
            return response()->json([
                'status' => 'success',
                'message' => 'Verification link sent succesfully',
            ]);

        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'User not found'
            ]);
        }


    }


    /**
     * 
     * Check if user has already been verified
     */

    public function checkIfEmailIsVerified($user)
    {
        if ($user->email_verified_at) {
            response()->json([
                "status" => 'failed',
                "message" => "Email has already been verified",
            ])->send();
            exit;

        }
    }
    /**
     * 
     * Verify user email
     */


    public function verifyEmail($email, $token)
    {
        $user = User::where("email", $email)->first();
        if (!$user) {
            response()->json([
                "status" => "failed",
                "message" => "User not found",
            ])->send();
            exit;
        }

        $this->checkIfEmailIsVerified($user);
        $verifiedToken = $this->verifyToken($email, $token);

        if ($user->markEmailAsVerified()) {
            $verifiedToken->delete();
            return response()->json([
                "status" => "success",
                "message" => "Email has been verified succesfully",
            ]);

        } else {
            return response()->json([
                "status" => "failed",
                "message" => "Email Verification failed, please try again later.",
            ]);
        }

    }


    /**
     * 
     * Verify token
     */

    public function verifyToken($email, $token)
    {
        $token = EmailVerificationToken::where('email', $email)->where('token', $token)->first();

        if ($token) {
            if ($token?->expired_at >= now()) {
                return $token;
            } else {
                response()->json([
                    'status' => 'failed',
                    'message' => 'Token Expired'
                ])->send();
                exit;
            }

        } else {
            response()->json([
                "status" => "failed",
                'message' => 'Invalid token'
            ])->send();

            exit;

        }
    }


}