<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if(Auth::guard('api')->user()){
            return [
                'password' => ['required', 'confirmed'],
                'email' => ['required', 'email'],
                'name' => ['required'],
            ];
        }else{
            return [
                'password' => ['required', 'confirmed'],
                'email' => ['required', 'email'],
                'name' => ['required'],
                'confirmation_code' => ['required', 'numeric'],
                'base64ImagePhoto' => ['nullable', 'string'],
                'phone_number' => ['required', 'unique:vcards,phone_number', 'regex:/^9\d{8}$/'],
            ];
        }
    }
}
