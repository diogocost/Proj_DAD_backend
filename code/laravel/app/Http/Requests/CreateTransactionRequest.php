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

        $user = auth('api')->user();
        $isAdmin = $user->isAdmin();


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
            'vcard' => ['required', 'exists:vcards,phone_number'],
        ];

        if($isAdmin){
            $rules['payment_reference'] =  $paymentTypeRules[$this->input('payment_type')];
            $rules['payment_type'] = ['required', 'in:IBAN,MBWAY,PAYPAL,MB,VISA'];
        } else {
            $rules['pair_vcard'] =  ['required', 'exists:vcards,phone_number', 'different:vcard'];
            $rules['payment_type'] = ['required', 'in:VCARD'];
            $rules['category_id'] = ['nullable', 'exists:categories,id'];
            $rules['description'] = ['nullable', 'string'];
            $rules['confirmation_code'] = ['required', 'numeric'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'payment_reference.regex' => $this->paymentReferenceErrorMessage(),
        ];
    }

    /**
     * Generate a custom error message for payment_reference based on payment_type.
     *
     * @return string
     */
    protected function paymentReferenceErrorMessage(): string
    {
        $paymentType = $this->input('payment_type');

        switch ($paymentType) {
            case 'IBAN':
                return 'The Payment reference must be 25 characters long, starting with 2 letters followed by 23 numbers.';
            case 'MBWAY':
                return 'The Payment reference must be a 9-digit phone number starting with 9.';
            case 'MB':
                return 'The Payment reference reference must be in the format 00000-000000000.';
            default:
                return 'Invalid payment reference format.';
        }
    }
}
