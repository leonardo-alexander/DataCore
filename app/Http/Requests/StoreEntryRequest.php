<?php

namespace App\Http\Requests;

use App\Models\Collection;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreEntryRequest extends FormRequest
{
    private const FILE_MIMES = 'jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv,txt';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->questions()->reduce(
            fn (array $rules, Question $question) => $rules + $this->rulesFor($question),
            ['answers' => ['required', 'array']] + $this->metadataRules(),
        );
    }

    public function messages(): array
    {
        $messages = $this->questions()->reduce(function (array $messages, Question $question) {
            $key = "answers.{$question->id}";

            return $messages + [
                "{$key}.required" => __('":title" is required.', ['title' => $question->title]),
                "{$key}.max"      => __('":title" must be 10 MB or smaller.', ['title' => $question->title]),
                "{$key}.mimes"    => __('":title" is not an accepted file type.', ['title' => $question->title]),
            ];
        }, []);

        return $messages + collect($this->missingMetadata())
            ->mapWithKeys(fn (string $key) => [
                "metadata.{$key}.required" => __('This survey needs your :field.', [
                    'field' => Str::lower(Collection::METADATA[$key] ?? $key),
                ]),
            ])
            ->all();
    }

    /**
     * Metadata the survey asked for that the respondent's profile cannot supply.
     * Asked for on the form itself, so the entry is never stored missing the very
     * fields the survey was set up to collect.
     *
     * @return array<int, string>
     */
    public function missingMetadata(): array
    {
        return $this->user()?->profile
            ?->missingMetadata($this->collection()->metadata_fields ?? [])
            ?? [];
    }

    private function metadataRules(): array
    {
        return collect($this->missingMetadata())
            ->mapWithKeys(fn (string $key) => ["metadata.{$key}" => match ($key) {
                'age'            => ['required', 'date', 'before_or_equal:' . now()->subYears(17)->format('Y-m-d')],
                'gender'         => ['required', Rule::in(UpdateProfileRequest::GENDERS)],
                'marital_status' => ['required', Rule::in(UpdateProfileRequest::MARITAL_STATUSES)],
                default          => ['required', 'string', 'max:255'],
            }])
            ->all();
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->questions()
                ->filter(fn (Question $question) => $this->usedOtherFor($question) && blank($this->otherTextFor($question)))
                ->each(fn (Question $question) => $validator->errors()->add(
                    "answers.{$question->id}",
                    __('Please type your "Other" answer for ":title".', ['title' => $question->title]),
                ));
        });
    }

    public function collection(): Collection
    {
        return $this->route('collection');
    }

    public function questions()
    {
        return $this->collection()->questions;
    }

    public function otherTextFor(Question $question): string
    {
        return trim((string) $this->input("other_texts.{$question->id}", ''));
    }

    private function usedOtherFor(Question $question): bool
    {
        $value = $this->input("answers.{$question->id}");

        return $value === '__other__' || (is_array($value) && in_array('__other__', $value, true));
    }

    private function rulesFor(Question $question): array
    {
        $key      = "answers.{$question->id}";
        $presence = $question->required ? 'required' : 'nullable';

        if ($question->type === 'file') {
            return [$key => [$presence, 'file', 'max:10240', 'mimes:' . self::FILE_MIMES]];
        }

        return $question->required ? [$key => ['required']] : [];
    }
}
