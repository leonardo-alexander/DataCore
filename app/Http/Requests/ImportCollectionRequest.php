<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price'       => ['nullable', 'integer', 'min:0', 'max:' . config('datacore.max_price')],
            'reward'      => ['nullable', 'integer', 'min:0', 'max:' . config('datacore.max_price')],
            // Draft only. A dataset that was imported a moment ago cannot have been
            // cleaned, and publishing is meant to be gated on Half Clean — allowing
            // "published" here let a crafted request put raw, un-stripped personal
            // data straight into the marketplace. The import form only ever sends
            // draft; the owner publishes from the editor after cleaning.
            'status'      => ['required', Rule::in(['draft'])],
            'csv_file'    => ['required', 'file', 'extensions:csv,txt', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.extensions' => __('Upload a .csv or .txt file.'),
            'csv_file.max'        => __('The CSV file may not be larger than 10 MB.'),
        ];
    }
}
