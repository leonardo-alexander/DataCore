<?php

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'            => ['required', 'integer', 'min:' . config('datacore.min_topup'), 'max:' . config('datacore.max_topup')],
            'method'            => ['required', Rule::in(['Virtual Account', 'QRIS', 'E-wallet', 'Card'])],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => __('Minimum top-up is :amount.', ['amount' => Money::format((int) config('datacore.min_topup'))]),
            'amount.max' => __('Maximum top-up is :amount.', ['amount' => Money::format((int) config('datacore.max_topup'))]),
        ];
    }
}
