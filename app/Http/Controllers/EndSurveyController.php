<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EndSurveyController extends Controller
{
    public function __construct(private WalletService $wallet) {}

    public function __invoke(string $locale, Collection $collection)
    {
        abort_unless($collection->user_id === Auth::id(), 403);
        abort_unless($collection->status === 'ongoing', 422);
        abort_if($collection->survey_ended_at !== null, 422);

        /** @var User $user */
        $user   = Auth::user();
        $refund = $collection->unusedRewardRefund();

        DB::transaction(function () use ($collection, $user, $refund) {
            $collection->update(['survey_ended_at' => now(), 'reward_budget' => 0, 'status' => 'draft']);

            if ($refund > 0) {
                $this->wallet->credit($user, $refund, 'escrow_refund', [
                    'collection_id' => $collection->id,
                    'description'   => 'Reward pool refund: ' . $collection->title . ' (survey ended, platform fee retained)',
                    'activity'      => \App\Support\Money::format($refund) . ' refunded after ending "' . $collection->title . '"',
                ]);
            }
        });

        return redirect()->route('collections.index')
            ->with('success', '"' . $collection->title . '" survey ended.' . ($refund > 0 ? ' ' . \App\Support\Money::format($refund) . ' refunded to your wallet (platform fee not refunded).' : ''));
    }
}
