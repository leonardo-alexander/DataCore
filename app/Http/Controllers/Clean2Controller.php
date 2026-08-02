<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCleaning;
use App\Models\Collection;
use App\Models\User;
use App\Services\CleaningService;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
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

        if ($count > config('datacore.cleaning_sync_limit')) {
            ProcessCleaning::dispatch($collection, $user, 'clean2');

            return back()->with('success', __('Large dataset. Clean 2 is running in the background. :amount will be charged once done.', [
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
        }

        return back()->with('success', __('Clean 2 complete! :amount charged.', ['amount' => Money::format($fee)]));
    }
}
