<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function note(): string
    {
        return $this->validated('note') ?: __('Your documents could not be verified. Please re-submit.');
    }
}
