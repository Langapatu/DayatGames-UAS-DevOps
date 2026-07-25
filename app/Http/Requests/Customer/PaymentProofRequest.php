<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        $method = $this->route('order')?->payment?->payment_method;
        $requiresReference = in_array($method, ['bank_transfer', 'e_wallet'], true);

        return [
            'payment_reference' => [
                Rule::requiredIf($requiresReference),
                'nullable',
                'string',
                'max:100',
            ],
            'payment_proof' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:4096',
            ],
        ];
    }
}
