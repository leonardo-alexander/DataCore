<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Activity;
use App\Models\Collection;
use App\Models\Purchase;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Auth::user()->purchases()
            ->with(['collection.user', 'collection.category'])
            ->latest()
            ->get();

        return view('purchases.index', compact('purchases'));
    }

    public function store(string $locale, Collection $collection, WalletService $wallet)
    {
        abort_unless($collection->status === 'published', 404);

        $user = Auth::user();

        if ($collection->user_id === $user->id) {
            return back()->with('error', 'You cannot purchase your own dataset.');
        }

        if ($collection->purchasedBy($user)) {
            return back()->with('error', 'You already own this dataset.');
        }

        $price = (int) $collection->price;

        try {
            DB::transaction(function () use ($collection, $user, $wallet, $price) {
                if ($price > 0) {
                    $wallet->debit($user, $price, 'payment', [
                        'collection_id' => $collection->id,
                        'description' => 'Purchased '.$collection->title,
                        'activity' => 'You purchased '.$collection->title,
                    ]);

                    $wallet->credit($collection->user, $price, 'payment', [
                        'collection_id' => $collection->id,
                        'description' => 'Sale of '.$collection->title,
                        'activity' => 'You sold '.$collection->title.' for '.\App\Support\Money::format($price, $collection->user),
                    ]);
                }

                Purchase::create([
                    'collection_id' => $collection->id,
                    'user_id' => $user->id,
                    'amount' => $price,
                ]);
            });
        } catch (InsufficientBalanceException $e) {
            return redirect()->route('wallet.index')->with('error', $e->getMessage());
        }

        return redirect()->route('marketplace.show', $collection)
            ->with('success', 'Purchase complete. You now have full access to "'.$collection->title.'".');
    }
}
