<?php

namespace App\Customs\Services;

use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordService
{
    private function validateCurrentPassword($current_password)
    {
        Log::debug('User Change Password: /api/change_password', ['CURRENT_password' => $current_password]);

        if (!password_verify($current_password, auth()->user()->password)) {
            response()->json([
                "status" => "failed",
                "message" => "Password didn't not match the current password"
            ])->send();
            exit;
        }
    }
    public function changePassword($data)
    {
        //password current password

        $this->validateCurrentPassword($data['current_password']);

        $updatePassword = auth()->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        if ($updatePassword) {

            return response()->json([
                'status' => 'success',
                'message' => 'password updated succesfully.'

            ]);

        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occured while updating password.'
            ]);
        }

    }

}