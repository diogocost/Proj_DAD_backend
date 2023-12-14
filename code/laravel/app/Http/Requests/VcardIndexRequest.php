<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VcardIndexRequest extends FormRequest
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
        return [
            'blocked' => ['sometimes', 'boolean'],
            'min_balance' => ['sometimes', 'numeric', 'min:0'],
            'max_balance' => ['sometimes', 'numeric', 'min:0', 'gte:min_balance'],
            'created_at_start' => ['sometimes', 'date'],
            'created_at_end' => ['sometimes', 'date', 'after_or_equal:created_at_start'],
        ];
    }
}
