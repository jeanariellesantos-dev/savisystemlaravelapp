<?php

namespace App\Http\Controllers\api\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use App\Customs\Services\PasswordService;
use Illuminate\Support\Facades\Log;

class PasswordController extends Controller
{
    //

    public function __construct(private PasswordService $service)
    {


    }

    public function changeUserPassword(ChangePasswordRequest $request)
    {

        return $this->service->changePassword($request->validated());


    }
}
