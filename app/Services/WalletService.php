<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Activity;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    public function credit(User $user, int $amount, string $type, array $options = []): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $options) {
            $wallet = $this->lockedWallet($user);
            $wallet->increment('balance', $amount);

            return $this->record($user, $amount, $type, 'credit', $options);
        });
    }

    public function debit(User $user, int $amount, string $type, array $options = []): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $options) {
            $wallet = $this->lockedWallet($user);

            if ($wallet->balance < $amount) {
                throw new InsufficientBalanceException();
            }

            $wallet->decrement('balance', $amount);

            return $this->record($user, $amount, $type, 'debit', $options);
        });
    }

    private function lockedWallet(User $user): Wallet
    {
        $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

        if (! $wallet) {
            $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 0]);
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
        }

        return $wallet;
    }

    private function record(User $user, int $amount, string $type, string $direction, array $options): Transaction
    {
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'payment_method_id' => $options['payment_method_id'] ?? null,
            'collection_id' => $options['collection_id'] ?? null,
            'reference' => 'TXN-'.strtoupper(Str::random(8)),
            'type' => $type,
            'direction' => $direction,
            'amount' => $amount,
            'status' => $options['status'] ?? 'success',
            'description' => $options['description'] ?? null,
        ]);

        if (! empty($options['activity'])) {
            Activity::log($user->id, $type, $options['activity'], $options['description'] ?? null);
        }

        return $transaction;
    }
}
