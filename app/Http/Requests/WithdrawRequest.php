<?php

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'         => ['required', 'integer', 'min:' . config('datacore.min_withdrawal'), 'max:' . max($this->user()->balance(), 1)],
            'bank_name'      => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name'   => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max' => __('The withdrawal amount exceeds your available balance.'),
            'amount.min' => __('Minimum withdrawal is :amount.', ['amount' => Money::format((int) config('datacore.min_withdrawal'))]),
        ];
    }
}
