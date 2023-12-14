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
            'start_date' => ['date'],
            'end_date' => ['date', 'after_or_equal:start_date'],
            'transaction_type' => ['in:D,C'],
            'category_id' => [
                'sometimes',
                'integer',
            ],
            'pair_vcard' => ['exists:vcards,phone_number'],
            'payment_type' => ['in:IBAN,VCARD,MBWAY,PAYPAL,MB,VISA'],
            'min_value' => ['numeric', 'min:0'],
            'max_value' => ['numeric', 'gte:min_value'],
        ];
    }
}
