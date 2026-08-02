<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectionRequest;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Question;
use App\Models\User;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CollectionController extends Controller
{
    public function __construct(private WalletService $wallet) {}

    public function index(Request $request)
    {
        $userId = Auth::id();
        $tab    = in_array($request->input('tab'), ['own', 'purchased']) ? $request->input('tab') : 'all';
        $search = trim($request->input('search', ''));
        $sort   = in_array($request->input('sort'), ['date', 'price', 'entries', 'reviews']) ? $request->input('sort') : 'date';
        $dir    = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $status = in_array($request->input('status'), ['draft', 'ongoing', 'published']) ? $request->input('status') : '';

        $query = Collection::query()
            ->with(['category'])
            ->withCount(['entries', 'reviews']);

        match ($tab) {
            'own'       => $query->where('user_id', $userId),
            'purchased' => $query->whereHas('purchases', fn($q) => $q->where('user_id', $userId))
                ->with(['purchases' => fn($q) => $q->where('user_id', $userId)]),
            default     => $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('purchases', fn($inner) => $inner->where('user_id', $userId));
            })->with(['purchases' => fn($q) => $q->where('user_id', $userId)]),
        };

        if ($search !== '') {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        match ($sort) {
            'price'   => $query->orderBy('price', $dir),
            'entries' => $query->orderBy('entries_count', $dir),
            'reviews' => $query->orderBy('reviews_count', $dir),
            default   => $query->orderBy('created_at', $dir),
        };

        $collections = $query->paginate(12)->withQueryString();
        $ownCount    = Collection::where('user_id', $userId)->count();
        $boughtCount = Collection::whereHas('purchases', fn($q) => $q->where('user_id', $userId))->count();

        return view('collections.index', compact('collections', 'tab', 'search', 'sort', 'dir', 'status', 'ownCount', 'boughtCount', 'userId'));
    }

    public function create()
    {
        return view('collections.create', [
            'categories'       => Category::orderBy('name')->get(),
            'typeOptions'      => Collection::questionTypes(),
            'initialQuestions' => [Collection::blankQuestion()],
            'feeRate'          => (float) config('datacore.platform_fee_rate'),
            'budgetLocked'     => false,
            'currentValues'    => [],
        ]);
    }

    public function store(StoreCollectionRequest $request)
    {
        $data = $request->validated();

        /** @var User $user */
        $user      = Auth::user();
        $reward    = (int) ($data['reward'] ?? 0);
        $target    = (int) ($data['respondent_target'] ?? 0);
        $feeRate   = (float) config('datacore.platform_fee_rate');
        $newStatus = $data['status'] ?? 'draft';
        $budget    = ($newStatus === 'ongoing' && $reward > 0 && $target > 0)
            ? (int) round($reward * $target * (1 + $feeRate))
            : 0;

        $collection = DB::transaction(function () use ($data, $user, $budget, $reward, $target, $newStatus) {
            $collection = $user->collections()->create([
                'category_id'       => $data['category_id'] ?? null,
                'title'             => $data['title'],
                'description'       => $data['description'] ?? null,
                'price'             => $data['price'] ?? 0,
                'reward'            => $reward,
                'respondent_target' => $target ?: null,
                'reward_budget'     => 0,
                'status'            => $newStatus,
                'metadata_fields'   => $data['metadata_fields'] ?? [],
                'type'              => 'survey',
            ]);

            $this->syncQuestions($collection, $data['questions'] ?? []);

            if ($budget > 0) {
                $this->wallet->debit($user, $budget, 'escrow', [
                    'collection_id' => $collection->id,
                    'description'   => __('Survey reward pool: :title', ['title' => $collection->title]),
                    'activity'      => __(':amount held as reward pool for ":title"', [
                        'amount' => Money::format($budget),
                        'title'  => $collection->title,
                    ]),
                ]);
                $collection->update(['reward_budget' => $budget]);
            }

            return $collection;
        });

        Activity::log(
            Auth::id(),
            'collection',
            __('Collection created'),
            __(':title is now :status.', ['title' => $collection->title, 'status' => $collection->status]),
        );

        return redirect()->route('collections.index')
            ->with('success', __('":title" was created successfully.', ['title' => $collection->title]));
    }

    public function edit(string $locale, Collection $collection)
    {
        $this->authorizeOwner($collection);
        $collection->load('questions');

        $pipelineState = $collection->cleanState();
        $pipelineCount = $collection->entries()->count();

        return view('collections.edit', [
            'collection'       => $collection,
            'categories'       => Category::orderBy('name')->get(),
            'typeOptions'      => Collection::questionTypes(),
            'initialQuestions' => Collection::questionsForForm($collection),
            'feeRate'          => (float) config('datacore.platform_fee_rate'),
            'budgetLocked'     => $collection->reward_budget > 0,
            'pipelineState'    => $pipelineState,
            'pipelineCount'    => $pipelineCount,
            'canPublish'       => $pipelineState !== 'raw',
            'clean2Fee'        => (int) config('datacore.clean2_fee'),
            'currentValues'    => [
                'title'             => $collection->title,
                'description'       => $collection->description ?? '',
                'price'             => $collection->price,
                'reward'            => $collection->reward,
                'respondent_target' => $collection->respondent_target ?? '',
                'status'            => $collection->status,
                'category_id'       => $collection->category_id ?? '',
                'metadata_fields'   => $collection->metadata_fields ?? [],
            ],
        ]);
    }

    public function update(string $locale, StoreCollectionRequest $request, Collection $collection)
    {
        $this->authorizeOwner($collection);
        $data = $request->validated();

        if ($data['status'] === 'published' && $collection->cleanState() === 'raw') {
            return back()
                ->withErrors(['status' => __('Clean 1 must be completed before this collection can be published.')])
                ->withInput();
        }

        /** @var User $user */
        $user = Auth::user();

        DB::transaction(function () use ($collection, $data, $user) {
            $oldStatus = $collection->status;
            $newStatus = $data['status'];
            $feeRate   = (float) config('datacore.platform_fee_rate');

            // Resolve reward fields (unlocked if no escrow, locked if budget is held)
            $rewardFields = $collection->reward_budget > 0
                ? ['reward' => $collection->reward, 'respondent_target' => $collection->respondent_target]
                : ['reward' => (int) ($data['reward'] ?? 0), 'respondent_target' => ($data['respondent_target'] ?: null)];

            // draft → ongoing: debit escrow now that the survey goes live
            if ($oldStatus === 'draft' && $newStatus === 'ongoing' && $collection->reward_budget === 0) {
                $reward = $rewardFields['reward'];
                $target = (int) ($rewardFields['respondent_target'] ?? 0);
                $budget = ($reward > 0 && $target > 0) ? (int) round($reward * $target * (1 + $feeRate)) : 0;

                if ($budget > 0) {
                    $this->wallet->debit($user, $budget, 'escrow', [
                        'collection_id' => $collection->id,
                        'description'   => __('Survey reward pool: :title', ['title' => $collection->title]),
                        'activity'      => __(':amount held as reward pool for ":title"', [
                            'amount' => Money::format($budget),
                            'title'  => $collection->title,
                        ]),
                    ]);
                    // New collecting round: clear the previous run's end marker so it can be ended again
                    $collection->update(['reward_budget' => $budget, 'survey_ended_at' => null]);
                    // Lock fields now that escrow is held
                    $rewardFields = ['reward' => $reward, 'respondent_target' => $target ?: null];
                }
            }

            // ongoing → draft (paused) or any → published: refund uncollected rewards (platform fee retained)
            if ($newStatus !== 'ongoing' && $collection->reward_budget > 0) {
                $refund = $collection->unusedRewardRefund();

                if ($refund > 0) {
                    $this->wallet->credit($user, $refund, 'escrow_refund', [
                        'collection_id' => $collection->id,
                        'description'   => __('Unused reward pool refund: :title', ['title' => $collection->title]),
                        'activity'      => __(':amount refunded from ":title" reward pool', [
                            'amount' => Money::format($refund),
                            'title'  => $collection->title,
                        ]),
                    ]);
                }

                $collection->update(['reward_budget' => 0]);
                // Unlock reward fields so they can be changed while paused
                $rewardFields = ['reward' => $collection->reward, 'respondent_target' => $collection->respondent_target];
            }

            $collection->update([
                'category_id'     => $data['category_id'] ?? null,
                'title'           => $data['title'],
                'description'     => $data['description'] ?? null,
                'price'           => $data['price'] ?? 0,
                'status'          => $newStatus,
                'metadata_fields' => $data['metadata_fields'] ?? [],
                ...$rewardFields,
            ]);

            $collection->questions()->delete();
            $this->syncQuestions($collection, $data['questions'] ?? []);
        });

        return redirect()->route('collections.index')
            ->with('success', __('":title" was updated.', ['title' => $collection->title]));
    }

    public function destroy(string $locale, Collection $collection)
    {
        $this->authorizeOwner($collection);

        /** @var User $user */
        $user = Auth::user();

        DB::transaction(function () use ($collection, $user) {
            // Refund uncollected rewards before deleting (platform fee retained)
            if ($collection->reward_budget > 0) {
                $refund = $collection->unusedRewardRefund();

                if ($refund > 0) {
                    $this->wallet->credit($user, $refund, 'escrow_refund', [
                        'collection_id' => $collection->id,
                        'description'   => __('Reward pool refund: :title (deleted)', ['title' => $collection->title]),
                        'activity'      => __(':amount refunded after deleting ":title"', [
                            'amount' => Money::format($refund),
                            'title'  => $collection->title,
                        ]),
                    ]);
                }
            }

            $collection->delete();
        });

        return redirect()->route('collections.index')
            ->with('success', __('":title" was deleted.', ['title' => $collection->title]));
    }

    public function analytics(string $locale, Collection $collection)
    {
        $this->authorizeOwner($collection);

        $totalEntries   = $collection->entries()->count();
        $target         = (int) ($collection->respondent_target ?? 0);
        $completionRate = $target > 0 ? min(100, round($totalEntries / $target * 100, 1)) : null;

        $withClean2 = $collection->entries()->whereNotNull('clean2_data')->count();
        $withClean1 = $collection->entries()->whereNotNull('clean1_data')->whereNull('clean2_data')->count();
        $withRaw    = $totalEntries - $withClean2 - $withClean1;

        $dailyData = $collection->entriesPerDay(30);

        return view('collections.analytics', [
            'collection'     => $collection,
            'totalEntries'   => $totalEntries,
            'target'         => $target,
            'completionRate' => $completionRate,
            'withClean2'     => $withClean2,
            'withClean1'     => $withClean1,
            'withRaw'        => $withRaw,
            'qualityScore'   => $collection->quality_score,
            'dailyData'      => $dailyData,
        ]);
    }

    public function export(string $locale, Collection $collection): StreamedResponse
    {
        abort_unless($collection->user_id === Auth::id() || $collection->purchasedBy(Auth::user()), 403);

        $collection->load(['questions', 'entries']);
        $metadataFields = $collection->metadata_fields ?? [];
        $metadataLabels = array_map(fn ($key) => Collection::METADATA[$key] ?? $key, $metadataFields);
        $headers        = [...$collection->questions->pluck('title')->all(), ...$metadataLabels];
        $filename       = $collection->slug . '-' . $collection->cleanState() . '.csv';

        return response()->streamDownload(function () use ($collection, $headers, $metadataFields) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);

            foreach ($collection->entries as $entry) {
                $source = $entry->clean2_data ?? $entry->clean1_data ?? $entry->raw_data ?? [];
                $row    = [];

                foreach ($collection->questions as $question) {
                    $row[] = $source[$question->title] ?? '';
                }

                foreach ($metadataFields as $key) {
                    $row[] = $entry->metadata[$key] ?? '';
                }

                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function syncQuestions(Collection $collection, array $questions): void
    {
        foreach (array_values($questions) as $i => $q) {
            if (empty($q['text'])) {
                continue;
            }

            $type    = $q['type'] ?? 'text';
            $options = array_values(array_filter($q['options'] ?? [], fn($o) => filled($o)));

            Question::create([
                'collection_id' => $collection->id,
                'title'         => $q['text'],
                'type'          => $type,
                'required'      => filter_var($q['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'options'       => in_array($type, ['choice', 'checkbox']) ? $options : null,
                'has_other'     => in_array($type, ['choice', 'checkbox']) && filter_var($q['has_other'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'max_stars'     => $type === 'rating' ? (int) ($q['max_stars'] ?? 5) : null,
                'date_mode'     => $type === 'datetime' ? ($q['date_mode'] ?? 'date') : null,
                'position'      => $i,
            ]);
        }
    }

    private function authorizeOwner(Collection $collection): void
    {
        abort_unless($collection->user_id === Auth::id(), 403);
    }
}
