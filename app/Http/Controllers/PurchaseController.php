<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Collection;
use App\Models\Purchase;
use App\Services\WalletService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $purchases = $user->purchases()
            ->with(['collection.user', 'collection.category'])
            ->latest()
            ->get();

        return view('purchases.index', compact('purchases'));
    }

    public function store(string $locale, Collection $collection, WalletService $wallet)
    {
        abort_unless($collection->status === 'published', 404);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($collection->user_id === $user->id) {
            return back()->with('error', __('You cannot purchase your own dataset.'));
        }

        if ($collection->purchasedBy($user)) {
            return back()->with('error', __('You already own this dataset.'));
        }

        $price = (int) $collection->price;

        try {
            DB::transaction(function () use ($collection, $user, $wallet, $price) {
                if ($price > 0) {
                    $wallet->debit($user, $price, 'payment', [
                        'collection_id' => $collection->id,
                        'description'   => __('Purchased :title', ['title' => $collection->title]),
                        'activity'      => __('You purchased :title', ['title' => $collection->title]),
                    ]);

                    $wallet->credit($collection->user, $price, 'payment', [
                        'collection_id' => $collection->id,
                        'description'   => __('Sale of :title', ['title' => $collection->title]),
                        'activity'      => __('You sold :title for :amount', [
                            'title'  => $collection->title,
                            'amount' => Money::format($price, $collection->user),
                        ]),
                    ]);
                }

                Purchase::create([
                    'collection_id' => $collection->id,
                    'user_id'       => $user->id,
                    'amount'        => $price,
                ]);
            });
        } catch (InsufficientBalanceException) {
            return redirect()->route('wallet.index')
                ->with('error', __('You do not have enough balance for this purchase. Top up and try again.'));
        }

        return redirect()->route('marketplace.show', $collection)
            ->with('success', __('Purchase complete. You now have full access to ":title".', ['title' => $collection->title]));
    }
}
