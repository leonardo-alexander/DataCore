<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public const GENDERS = ['Prefer not to say', 'Male', 'Female', 'Other'];

    public const MARITAL_STATUSES = ['Single', 'Married', 'Divorced', 'Widowed', 'Prefer not to say'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'gender'       => ['nullable', Rule::in(self::GENDERS)],
            'dob'          => ['nullable', 'date', 'before_or_equal:' . now()->subYears(17)->format('Y-m-d')],
            'address'      => ['nullable', 'string', 'max:1000'],
            // Surveys can ask for these three as respondent metadata, so they have
            // to be capturable here — otherwise the snapshot taken on submit is
            // always empty for them.
            'city'           => ['nullable', 'string', 'max:255'],
            'profession'     => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', Rule::in(self::MARITAL_STATUSES)],
            'image'        => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'dob.before_or_equal' => __('You must be at least 17 years old.'),
        ];
    }
}
