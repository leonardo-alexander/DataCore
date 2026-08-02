<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use App\Models\Verification;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('datacore.admin');

        $user = User::updateOrCreate(
            ['email' => $admin['email']],
            [
                'name'              => $admin['name'],
                'password'          => $admin['password'],
                'is_admin'          => true,
                'email_verified_at' => now(),
            ]
        );

        Profile::firstOrCreate(['user_id' => $user->id]);
        Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        Verification::firstOrCreate(['user_id' => $user->id], ['status' => 'verified']);
    }
}
