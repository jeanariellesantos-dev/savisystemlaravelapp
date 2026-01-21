<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResendEmailVerificationLinkRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Customs\Services\EmailVerificationService;
use App\Models\User;

class UserController extends Controller
{

    public function __construct(private EmailVerificationService $service)
    {
    }
    //
    public function login(LoginRequest $request)
    {

        $token = auth()->attempt($request->validated());

        if ($token) {
            return $this->responseWithToken($token, auth()->user());
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid Credentials',
            ], 401);
        }

    }

    public function store(StoreUserRequest $request)
    {
        if ($request->validated()) {
            $user = User::create($request->validated());

            if ($user) {

                $this->service->sendVerificationLink($user);

                $token = auth()->login($user);
                return $this->responseWithToken($token, $user);

            } else {

                return response()->json([
                    'status' => 'failed',
                    'message' => 'an error while trying to create user',
                ], 500);

            }

            //  return response()->json(["message" => "account created succesfully"]);

        }

    }

    public function responseWithToken($token, $user)
    {
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'access_token' => $token,
            'type' => 'bearer',
        ]);

    }

    public function verifyUserEmail(VerifyEmailRequest $request)
    {
        return $this->service->verifyEmail($request->email, $request->token);

    }


    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request)
    {

        return $this->service->resendLink($request->email);


    }




}
