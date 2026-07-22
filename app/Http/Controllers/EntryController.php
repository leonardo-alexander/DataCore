<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EntryController extends Controller
{
    public function create(string $locale, Collection $collection)
    {
        abort_unless($collection->status === 'ongoing', 404);

        if ($collection->survey_ended_at !== null) {
            return redirect()->route('surveys.index')
                ->with('error', 'This survey has been ended by its creator and is no longer accepting responses.');
        }

        /** @var User $user */
        $user = Auth::user();

        if ($collection->user_id === $user->id) {
            return redirect()->route('surveys.index')
                ->with('error', 'You cannot fill your own survey.');
        }

        $user->loadMissing('verification');

        if ($user->verification?->status !== 'verified') {
            return redirect()->route('verification.index')
                ->with('error', 'Identity verification is required before filling surveys.');
        }

        if ($collection->entries()->where('user_id', $user->id)->exists()) {
            return redirect()->route('surveys.index')
                ->with('error', 'You have already submitted a response to this survey.');
        }

        if ($collection->respondent_target && $collection->entries()->count() >= $collection->respondent_target) {
            return redirect()->route('surveys.index')
                ->with('error', 'This survey has reached its target and is no longer accepting responses.');
        }

        $collection->load('questions');

        return view('entries.create', compact('collection'));
    }

    public function store(string $locale, Request $request, Collection $collection, WalletService $wallet)
    {
        abort_unless($collection->status === 'ongoing', 404);

        if ($collection->survey_ended_at !== null) {
            return redirect()->route('surveys.index')
                ->with('error', 'This survey has been ended by its creator and is no longer accepting responses.');
        }

        /** @var User $user */
        $user = Auth::user();

        if ($collection->user_id === $user->id) {
            return redirect()->route('surveys.index')
                ->with('error', 'You cannot fill your own survey.');
        }

        $user->loadMissing('verification');

        if ($user->verification?->status !== 'verified') {
            return redirect()->route('verification.index')
                ->with('error', 'Identity verification is required before filling surveys.');
        }

        if ($collection->entries()->where('user_id', $user->id)->exists()) {
            return redirect()->route('surveys.index')
                ->with('error', 'You have already submitted a response to this survey.');
        }

        if ($collection->respondent_target && $collection->entries()->count() >= $collection->respondent_target) {
            return redirect()->route('surveys.index')
                ->with('error', 'This survey has reached its target and is no longer accepting responses.');
        }

        $collection->load('questions');

        $rules    = ['answers' => ['required', 'array']];
        $messages = [];

        foreach ($collection->questions as $question) {
            $key = "answers.{$question->id}";

            if ($question->type === 'file') {
                $fileRules = ['file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx,csv,txt'];
                $rules[$key]              = [$question->required ? 'required' : 'nullable', ...$fileRules];
                $messages["{$key}.required"] = '"' . $question->title . '" is required.';
                $messages["{$key}.max"]      = '"' . $question->title . '" must be 10 MB or smaller.';
                $messages["{$key}.mimes"]    = '"' . $question->title . '" is not an accepted file type.';
                continue;
            }

            if ($question->required) {
                $rules[$key]              = ['required'];
                $messages["{$key}.required"] = '"' . $question->title . '" is required.';
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($validator) use ($collection, $request) {
            foreach ($collection->questions as $question) {
                $value     = $request->input("answers.{$question->id}");
                $usedOther = $value === '__other__' || (is_array($value) && in_array('__other__', $value, true));

                if ($usedOther && blank(trim((string) $request->input("other_texts.{$question->id}", '')))) {
                    $validator->errors()->add("answers.{$question->id}", 'Please type your "Other" answer for "' . $question->title . '".');
                }
            }
        });

        $validator->validate();

        $answers    = $request->input('answers', []);
        $otherTexts = $request->input('other_texts', []);
        $raw = [];

        foreach ($collection->questions as $question) {
            if ($question->type === 'file') {
                if ($file = $request->file("answers.{$question->id}")) {
                    $raw[$question->title] = '/storage/' . $file->store('survey-uploads', 'public');
                }
                continue;
            }

            $value = $answers[$question->id] ?? null;
            $other = trim((string) ($otherTexts[$question->id] ?? ''));

            if (is_array($value)) {
                $value = implode(', ', array_filter(
                    array_map(fn ($v) => $v === '__other__' ? $other : $v, $value),
                    fn ($v) => filled($v)
                ));
            } elseif ($value === '__other__') {
                $value = $other;
            }

            if (filled($value)) {
                $raw[$question->title] = trim((string) $value);
            }
        }

        if (empty($raw)) {
            return back()->with('error', 'Please answer at least one question before submitting.')->withInput();
        }

        Entry::create([
            'collection_id' => $collection->id,
            'user_id'       => $user->id,
            'raw_data'      => $raw,
            'metadata'      => $this->snapshotMetadata($user, $collection),
            'status'        => 'raw',
        ]);

        $reward = (int) $collection->reward;

        if ($reward > 0) {
            $wallet->credit($user, $reward, 'reward', [
                'collection_id' => $collection->id,
                'description'   => 'Entry reward: ' . $collection->title,
                'activity'      => 'You earned ' . \App\Support\Money::format($reward) . ' for contributing',
            ]);
        }

        Activity::log($collection->user_id, 'entry', 'New entry received', $collection->title . ' got a new submission.');

        return redirect()->route('dashboard')
            ->with('success', 'Thanks for contributing! ' . \App\Support\Money::format($reward) . ' was added to your wallet.');
    }

    private function snapshotMetadata(User $user, Collection $collection): array
    {
        $requested = $collection->metadata_fields ?? [];

        if (! $requested) {
            return [];
        }

        $profile = $user->profile?->metadata() ?? [];

        return collect($profile)->only($requested)->filter(fn ($v) => filled($v))->all();
    }
}
