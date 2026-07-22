<?php

namespace App\Http\Requests;

use App\Models\Collection;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'              => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:2000'],
            'category_id'        => ['nullable', 'exists:categories,id'],
            'price'              => ['nullable', 'integer', 'min:0', 'max:' . config('datacore.max_price')],
            'reward'             => ['nullable', 'integer', 'min:0', 'max:' . config('datacore.max_price')],
            'respondent_target'  => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'status'             => ['in:draft,ongoing,published'],

            'metadata_fields'    => ['array'],
            'metadata_fields.*'  => [Rule::in(array_keys(Collection::METADATA))],

            'questions'          => ['required', 'array', 'min:1'],
            'questions.*.text'   => ['required', 'string', 'max:500'],
            'questions.*.type'   => ['required', Rule::in(Question::TYPES)],
            'questions.*.description' => ['nullable', 'string', 'max:500'],
            'questions.*.required'    => ['boolean'],
            'questions.*.has_other'   => ['boolean'],
            'questions.*.max_stars'   => ['nullable', 'integer', 'min:3', 'max:10'],
            'questions.*.date_mode'   => ['nullable', Rule::in(['date', 'time', 'datetime-local'])],
            'questions.*.options'     => ['array'],
            'questions.*.options.*'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Reward and target are mandatory while the survey is collecting responses
            if ($this->input('status') === 'ongoing') {
                if ((int) $this->input('reward', 0) < 1) {
                    $validator->errors()->add('reward', 'Entry reward is required when the survey is collecting responses.');
                }
                if ((int) $this->input('respondent_target', 0) < 1) {
                    $validator->errors()->add('respondent_target', 'A respondent target is required when the survey is collecting responses.');
                }
            }

            foreach ((array) $this->input('questions', []) as $i => $q) {
                if (in_array($q['type'] ?? null, ['choice', 'checkbox'], true)) {
                    $options  = array_filter($q['options'] ?? [], fn ($o) => filled(trim((string) $o)));
                    $hasOther = filter_var($q['has_other'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    if (count($options) + ($hasOther ? 1 : 0) < 2) {
                        $validator->errors()->add("questions.$i.options", 'Add at least two options for this question.');
                    }
                }
            }

            // Check balance whenever escrow would be debited: creating as ongoing OR transitioning draft → ongoing
            $collection = $this->route('collection');
            $newStatus  = $this->input('status');
            $oldStatus  = is_object($collection) ? $collection->status : null;

            $needsEscrow = (! $collection && $newStatus === 'ongoing') ||
                ($collection && $oldStatus === 'draft' && $newStatus === 'ongoing'
                    && is_object($collection) && $collection->reward_budget === 0);

            if ($needsEscrow) {
                $reward  = (int) $this->input('reward', is_object($collection) ? $collection->reward : 0);
                $target  = (int) $this->input('respondent_target', is_object($collection) ? $collection->respondent_target : 0);
                $feeRate = (float) config('datacore.platform_fee_rate');
                $budget  = ($reward > 0 && $target > 0) ? (int) round($reward * $target * (1 + $feeRate)) : 0;

                if ($budget > 0 && $budget > $this->user()->balance()) {
                    $validator->errors()->add('reward', 'Reward pool exceeds your wallet balance — top up first.');
                }
            }
        });
    }
}
