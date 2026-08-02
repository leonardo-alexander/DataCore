<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public const PREFERENCES = ['notify_sales', 'notify_rewards', 'notify_cleaning', 'notify_marketing'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency'         => ['nullable', Rule::in(['IDR', 'USD'])],
            'password'         => ['nullable', 'required_with:password_confirmation', 'string', 'min:8', 'confirmed'],
            'notify_sales'     => ['nullable', 'boolean'],
            'notify_rewards'   => ['nullable', 'boolean'],
            'notify_cleaning'  => ['nullable', 'boolean'],
            'notify_marketing' => ['nullable', 'boolean'],
        ];
    }

    public function preferences(): Collection
    {
        return collect($this->validated())
            ->only(self::PREFERENCES)
            ->reject(fn ($value) => $value === null)
            ->map(fn ($value) => (bool) $value);
    }
}
