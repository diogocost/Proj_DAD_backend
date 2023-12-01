<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        $rules = [
            'password' => ['required', 'confirmed'],
            'email' => ['required', 'email'],
            'name' => ['required'],
            'user_type' => ['required', 'in:A,V'],
            'confirmation_code' => ['required_if:user_type,V', 'numeric', 'digits:4'],
            'photo_url' => ['optional|nullable|file|image'],
            'phone_number' => ['required_if:user_type,V', 'unique:vcards,phone_number', 'phone:PT']
        ];

        return $rules;
    }
}
