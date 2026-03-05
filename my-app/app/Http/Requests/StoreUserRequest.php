<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;

    }

    public function rules(): array
    {

        return [
            "firstname" => 'required|string|max:255',
            "lastname" => 'required|string|max:255',
            "mobile" => 'required|string|max:255',
            "role_id" => 'required|integer|min:1',
            "dealership_id" => 'nullable|exists:dealerships,id',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];

    }


}

?>