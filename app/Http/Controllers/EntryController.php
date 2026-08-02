<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntryRequest;
use App\Models\Activity;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Question;
use App\Models\User;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;

class EntryController extends Controller
{
    public function create(string $locale, Collection $collection)
    {
        $collection->load('questions');

        return view('entries.create', compact('collection'));
    }

    public function store(string $locale, StoreEntryRequest $request, Collection $collection, WalletService $wallet)
    {
        /** @var User $user */
        $user = Auth::user();

        $raw = $this->answersFor($request, $collection);

        if ($raw->isEmpty()) {
            return back()->with('error', __('Please answer at least one question before submitting.'))->withInput();
        }

        Entry::create([
            'collection_id' => $collection->id,
            'user_id'       => $user->id,
            'raw_data'      => $raw->all(),
            'metadata'      => $this->snapshotMetadata($user, $collection),
            'status'        => 'raw',
        ]);

        $reward = (int) $collection->reward;

        if ($reward > 0) {
            $wallet->credit($user, $reward, 'reward', [
                'collection_id' => $collection->id,
                'description'   => __('Entry reward: :title', ['title' => $collection->title]),
                'activity'      => __('You earned :amount for contributing', ['amount' => Money::format($reward)]),
            ]);
        }

        Activity::log(
            $collection->user_id,
            'entry',
            __('New entry received'),
            __(':title got a new submission.', ['title' => $collection->title]),
        );

        return redirect()->route('dashboard')->with('success', $reward > 0
            ? __('Thanks for contributing! :amount was added to your wallet.', ['amount' => Money::format($reward)])
            : __('Thanks for contributing!'));
    }

    private function answersFor(StoreEntryRequest $request, Collection $collection)
    {
        $answers = collect($request->input('answers', []));

        return $collection->questions
            ->mapWithKeys(function (Question $question) use ($request, $answers) {
                if ($question->type === 'file') {
                    $file = $request->file("answers.{$question->id}");

                    return $file ? [$question->title => '/storage/' . $file->store('survey-uploads', 'public')] : [];
                }

                $value = $this->resolveAnswer($answers->get($question->id), $request->otherTextFor($question));

                return filled($value) ? [$question->title => trim((string) $value)] : [];
            })
            ->filter(fn ($value) => filled($value));
    }

    private function resolveAnswer(mixed $value, string $otherText): mixed
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => $item === '__other__' ? $otherText : $item)
                ->filter(fn ($item) => filled($item))
                ->implode(', ');
        }

        return $value === '__other__' ? $otherText : $value;
    }

    private function snapshotMetadata(User $user, Collection $collection): array
    {
        $requested = $collection->metadata_fields ?? [];

        if (! $requested) {
            return [];
        }

        return collect($user->profile?->metadata() ?? [])
            ->only($requested)
            ->filter(fn ($value) => filled($value))
            ->all();
    }
}
