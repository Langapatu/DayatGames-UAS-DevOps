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
            'checkout_token' => ['required', 'uuid'],
            'payment_method' => ['required', Rule::in(['virtual_account', 'bank_transfer', 'e_wallet'])],
        ];
    }
}
