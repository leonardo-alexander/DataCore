<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCleaning;
use App\Models\Collection;
use App\Models\User;
use App\Services\CleaningService;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class Clean2Controller extends Controller
{
    public function __invoke(string $locale, Collection $collection, CleaningService $cleaning, WalletService $wallet)
    {
        abort_unless($collection->user_id === Auth::id(), 403);

        if ($collection->cleanState() === 'raw') {
            return back()->with('error', __('Run Clean 1 first before applying Clean 2.'));
        }

        $count = $collection->entries()->count();

        if ($count === 0) {
            return back()->with('error', __('This collection has no entries to refine yet.'));
        }

        /** @var User $user */
        $user = Auth::user();
        $fee  = (int) config('datacore.clean2_fee');

        if ($user->balance() < $fee) {
            return redirect()->route('wallet.index')
                ->with('error', __('Clean 2 costs :amount. Please top up your wallet first.', ['amount' => Money::format($fee)]));
        }

        // Guards against a second run being started while one is still in flight —
        // for Clean 2 that would also charge the fee twice.
        $lock = Cache::lock(ProcessCleaning::lockKey($collection), ProcessCleaning::LOCK_SECONDS);

        if (! $lock->get()) {
            return back()->with('error', __('A clean is already running for this collection. Please wait for it to finish.'));
        }

        if (ProcessCleaning::shouldQueue($count)) {
            ProcessCleaning::dispatch($collection, $user, 'clean2', $lock->owner());

            // See Clean1Controller: with the sync connection the dispatch above
            // already did the work inside this request.
            return back()->with('success', config('queue.default') === 'sync'
                ? __('Large dataset. Clean 2 has been processed — check your activity feed for the result.')
                : __('Large dataset. Clean 2 is running in the background. :amount will be charged once done.', [
                    'amount' => Money::format($fee),
                ]));
        }

        try {
            $payload = $cleaning->process($collection, 'clean2');
            $cleaning->apply($collection, $payload, 'clean2');

            $wallet->debit($user, $fee, 'payment', [
                'collection_id' => $collection->id,
                'description'   => __('Clean 2 premium refine: :title', ['title' => $collection->title]),
            ]);
        } catch (Throwable $e) {
            Log::error('Clean 2 failed', ['collection_id' => $collection->id, 'error' => $e->getMessage()]);

            return back()->with('error', __('Clean 2 could not be completed. Please try again in a moment.'));
        } finally {
            $lock->release();
        }

        return back()->with('success', __('Clean 2 complete! :amount charged.', ['amount' => Money::format($fee)]));
    }
}
