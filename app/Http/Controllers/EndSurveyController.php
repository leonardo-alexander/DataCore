<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\User;
use App\Services\WalletService;
use App\Support\Money;
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
                    'description'   => __('Reward pool refund: :title (survey ended, platform fee retained)', ['title' => $collection->title]),
                    'activity'      => __(':amount refunded after ending ":title"', [
                        'amount' => Money::format($refund),
                        'title'  => $collection->title,
                    ]),
                ]);
            }
        });

        $message = __('":title" survey ended.', ['title' => $collection->title]);

        if ($refund > 0) {
            $message .= ' ' . __(':amount refunded to your wallet (platform fee not refunded).', [
                'amount' => Money::format($refund),
            ]);
        }

        return redirect()->route('collections.index')->with('success', $message);
    }
}
