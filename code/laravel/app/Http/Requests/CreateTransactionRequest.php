<?php

namespace App\Http\Requests;

use App\Models\Vcard;
use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
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

        
        $isAdmin = auth('api')->user()->isAdmin();

        $paymentTypeRules = [
            'IBAN' => ['required', 'regex:/^[A-Za-z]{2}\d{23}$/'],
            'VCARD' => ['required', 'exists:vcards,phone_number'],
            'MBWAY' => ['required', 'regex:/^9\d{8}$/'],
            'PAYPAL' => ['required', 'email'],
            'MB' => ['required', 'regex:/^\d{5}-\d{9}$/'],
            'VISA' => ['required', 'numeric', 'digits:16', 'starts_with:4'],
        ];

        $rules = [
            'value' => ['required', 'numeric', 'min:0.01'],
        ];

        if($isAdmin){
            $rules['vcard'] = ['required', 'exists:vcards,phone_number'];
            $rules['payment_reference'] =  $paymentTypeRules[$this->input('payment_type')];
            $rules['payment_type'] = ['required', 'in:IBAN,MBWAY,PAYPAL,MB,VISA'];
        } else {
            $rules['pair_vcard'] =  ['required', 'exists:vcards,phone_number'];
            $rules['payment_type'] = ['required', 'in:VCARD'];
            $rules['category_id'] = ['nullable', 'exists:categories,id'];
            $rules['description'] = ['nullable', 'string'];
        }

        return $rules;
    }
}
