<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->exists()) {
            $this->command?->warn('Users already exist — skipping DemoSeeder.');
        } else {
            $this->call(DemoSeeder::class);
        }

        $this->call(AdminSeeder::class);
    }
}
