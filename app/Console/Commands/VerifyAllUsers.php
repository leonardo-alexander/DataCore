<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Usability-testing helper: clears the identity-review queue so participants are
 * never blocked waiting on an admin. Refuses to run unless AUTO_VERIFY_USERS is
 * on, so it can't mass-verify a real deployment by accident.
 */
class VerifyAllUsers extends Command
{
    protected $signature = 'users:verify-all';

    protected $description = 'Mark every account verified (requires AUTO_VERIFY_USERS=true)';

    public function handle(): int
    {
        if (! config('datacore.auto_verify')) {
            $this->warn('AUTO_VERIFY_USERS is not enabled — nothing changed.');

            return self::SUCCESS;
        }

        $verified = 0;

        User::query()->chunkById(200, function ($users) use (&$verified) {
            foreach ($users as $user) {
                $user->verification()->updateOrCreate([], ['status' => 'verified', 'note' => null]);
                $verified++;
            }
        });

        $this->info("Verified {$verified} account(s).");

        return self::SUCCESS;
    }
}
