<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('required')->default(false)->after('options');
            $table->unsignedTinyInteger('max_stars')->nullable()->after('required');
            $table->string('date_mode')->nullable()->after('max_stars'); // date | time | datetime-local
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['required', 'max_stars', 'date_mode']);
        });
    }
};
