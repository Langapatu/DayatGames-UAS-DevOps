<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::in(['virtual_account', 'bank_transfer', 'e_wallet'])],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'payment_proof' => [
                Rule::requiredIf(fn () => in_array($this->input('payment_method'), ['bank_transfer', 'e_wallet'], true)),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:4096',
            ],
        ];
    }
}
