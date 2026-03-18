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
use Illuminate\Validation\Rule;
use Laravel\Pail\ValueObjects\Origin\Console;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::query()
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('dealerships', 'users.dealership_id', '=', 'dealerships.id')
            ->select([
                'users.id as user_id',
                'users.firstname',
                'users.lastname',
                'roles.id as role_id',
                'roles.role_name',
                'dealerships.id as dealership_id',
                'dealerships.dealership_name',
            ]);

        // ✅ Filter by role (optional)
        if ($request->filled('role')) {
            $query->where('roles.role_name', strtoupper($request->role));
        }

        // ✅ Only active users (recommended)
        $query->where('users.is_active', 1);

        return response()->json([
            'data' => $query->get()
        ]);
    }

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

    public function logout()
    {
        Auth()->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'User has been logged out',
        ]);
    }

    public function store(StoreUserRequest $request)
    {
    
        if ($request->validated()) {
            $user = User::create($request->validated());

            if ($user) {

            //    $this->service->sendVerificationLink($user);

                $token = auth()->login($user);
                return $this->responseWithToken($token, $user);

            } else {

                return response()->json([
                    'status' => 'failed',
                    'message' => 'an error while trying to create user',
                ], 500);

            }

             return response()->json(["message" => "account created succesfully"]);

        }

    }

    public function responseWithToken($token, $user)
    {
    $user->load('role');

    return response()->json([
        'status' => 'success',
        'user' => [
            'id' => $user->id,
            'employee_number' => $user->employee_number,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'role' => $user->role?->role_name,
            'role_description' => $user->role?->role_description, 
        ],
        'token' => $token,
        'type' => 'bearer',
        ])->cookie(
            'token',
            $token,
            60,     // minutes
            '/',
            'localhost',
            false,  // secure
            true    // HttpOnly
        );

    }

    public function verifyUserEmail(VerifyEmailRequest $request)
    {
        return $this->service->verifyEmail($request->email, $request->token);

    }


    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request)
    {

        return $this->service->resendLink($request->email);


    }


    public function update(Request $request)
    {

        $user = auth()->user();

        $data = $request->validate([
            'employee_number' => 'required|string',
            'firstname' => 'required|string',
            'lastname'  => 'required|string',
            // 'email'     => 'required|email',
            'mobile'    => 'nullable|string',
        ]);

        $user->update($data);

        return response()->json($user);

    }

    public function updateEmail(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        // Update email
        $user->email = $data['email'];

        // If you use email verification, reset status
        if ($user->email !== $data['email']) {
            $user->email_verified_at = null;
        }

        $user->save();

        // OPTIONAL: send verification email again
        // $this->service->sendVerificationLink($user);

        return response()->json([
            'status' => 'success',
            'email' => $user->email,
            'message' => 'Email updated successfully',
        ]);
    }

}
