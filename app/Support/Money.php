<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Money
{
    /**
     * Amounts are stored in IDR; display currency follows the given user's
     * setting, or the authenticated user's when none is passed (e.g. for
     * messages addressed to someone other than the current request's user,
     * such as the other party in a transaction).
     */
    public static function currency(?User $user = null): string
    {
        return ($user ?? Auth::user())?->currency ?? 'IDR';
    }

    public static function rate(): float
    {
        return (float) config('datacore.usd_rate', 16000);
    }

    public static function format(float|int|string|null $amountIdr, ?User $user = null): string
    {
        if (self::currency($user) === 'USD') {
            return '$' . number_format((float) $amountIdr / self::rate(), 2);
        }

        return 'Rp ' . number_format((float) $amountIdr, 0, ',', '.');
    }
}
