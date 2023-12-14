<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryRequest extends FormRequest
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
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'transaction_type' => ['sometimes', 'in:D,C'],
            'category_id' => [
                'sometimes',
                'integer',
            ],
            'pair_vcard' => ['sometimes', 'exists:vcards,phone_number'],
            'payment_type' => ['sometimes', 'in:IBAN,VCARD,MBWAY,PAYPAL,MB,VISA'],
            'min_value' => ['sometimes', 'numeric', 'min:0.01'],
            'max_value' => ['sometimes', 'numeric', 'min:0.01', 'gte:min_value'],
        ];
    }
}
