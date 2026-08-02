<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use App\Models\Verification;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = config('datacore.admin');
        $existing = User::where('email', $admin['email'])->first();
        $password = $admin['password'];

        if (blank($password)) {
            if (! app()->isProduction()) {
                $password = 'password';
            } elseif ($existing) {
                $password = null;
            } else {
                $password = Str::password(24, symbols: false);

                $this->command?->warn('ADMIN_PASSWORD is not set. Generated one for this deploy:');
                $this->command?->warn('    ' . $password);
                $this->command?->warn('Save it now, then set ADMIN_PASSWORD to choose your own.');
            }
        }

        $attributes = [
            'name'              => $admin['name'],
            'is_admin'          => true,
            'email_verified_at' => now(),
        ];

        if (filled($password)) {
            $attributes['password'] = $password;
        }

        $user = User::updateOrCreate(['email' => $admin['email']], $attributes);

        Profile::firstOrCreate(['user_id' => $user->id]);
        Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        Verification::firstOrCreate(['user_id' => $user->id], ['status' => 'verified']);
    }
}
