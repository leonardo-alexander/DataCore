<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCleaning;
use App\Models\Collection;
use App\Models\User;
use App\Services\CleaningService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Clean2Controller extends Controller
{
    public function __invoke(string $locale, Collection $collection, CleaningService $cleaning, WalletService $wallet)
    {
        abort_unless($collection->user_id === Auth::id(), 403);

        if ($collection->cleanState() === 'raw') {
            return back()->with('error', 'Run Clean 1 first before applying Clean 2.');
        }

        $count = $collection->entries()->count();

        if ($count === 0) {
            return back()->with('error', 'This collection has no entries to refine yet.');
        }

        /** @var User $user */
        $user = Auth::user();
        $fee  = (int) config('datacore.clean2_fee');

        if ($user->balance() < $fee) {
            return redirect()->route('wallet.index')
                ->with('error', 'Clean 2 costs ' . \App\Support\Money::format($fee) . '. Please top up your wallet first.');
        }

        if ($count > config('datacore.cleaning_sync_limit')) {
            ProcessCleaning::dispatch($collection, $user, 'clean2');
            return back()->with('success', 'Large dataset. Clean 2 is running in the background. ' . \App\Support\Money::format($fee) . ' will be charged once done.');
        }

        try {
            $payload = $cleaning->process($collection, 'clean2');
            $cleaning->apply($collection, $payload, 'clean2');
            $wallet->debit($user, $fee, 'payment', [
                'collection_id' => $collection->id,
                'description'   => 'Clean 2 premium refine: ' . $collection->title,
            ]);
        } catch (Throwable $e) {
            return back()->with('error', 'Clean 2 failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Clean 2 complete! ' . \App\Support\Money::format($fee) . ' charged.');
    }
}
