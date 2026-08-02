<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_number'      => ['required', 'string', 'max:50'],
            'id_card'        => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'selfie'         => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'phone_number'   => ['nullable', 'string', 'max:20'],
            'gender'         => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'dob'            => ['nullable', 'date', 'before:today', 'before_or_equal:' . now()->subYears(17)->format('Y-m-d')],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:100'],
            'profession'     => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', Rule::in(['Single', 'Married', 'Divorced', 'Widowed'])],
        ];
    }

    public function messages(): array
    {
        return [
            'dob.before_or_equal' => __('You must be at least 17 years old.'),
        ];
    }

    public function profileFields(): array
    {
        return collect($this->validated())
            ->only(['phone_number', 'gender', 'dob', 'address', 'city', 'profession', 'marital_status'])
            ->all();
    }
}
