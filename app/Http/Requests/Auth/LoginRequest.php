<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Browsers submit a ticked checkbox as "on", which the boolean rule rejects.
        $this->merge(['remember' => $this->boolean('remember')]);
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    public function credentials(): array
    {
        return $this->only('email', 'password');
    }
}
