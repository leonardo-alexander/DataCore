<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->unsignedInteger('respondent_target')->nullable()->after('reward');
            $table->unsignedBigInteger('reward_budget')->default(0)->after('respondent_target');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn(['respondent_target', 'reward_budget']);
        });
    }
};
