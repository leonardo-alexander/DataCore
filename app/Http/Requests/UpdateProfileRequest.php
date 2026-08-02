<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
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
            'gender'       => ['nullable', Rule::in(['Male', 'Female', 'Other', 'Prefer not to say'])],
            'dob'          => ['nullable', 'date', 'before_or_equal:' . now()->subYears(17)->format('Y-m-d')],
            'address'      => ['nullable', 'string', 'max:1000'],
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
