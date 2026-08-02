<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'    => ['required', Rule::in(['bank', 'ewallet', 'card'])],
            'name'    => ['required', 'string', 'max:255'],
            'account' => ['required', 'string', 'max:255'],
        ];
    }
}
