<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_sales')->default(true)->after('currency');
            $table->boolean('notify_rewards')->default(true)->after('notify_sales');
            $table->boolean('notify_cleaning')->default(true)->after('notify_rewards');
            $table->boolean('notify_marketing')->default(false)->after('notify_cleaning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_sales', 'notify_rewards', 'notify_cleaning', 'notify_marketing']);
        });
    }
};
