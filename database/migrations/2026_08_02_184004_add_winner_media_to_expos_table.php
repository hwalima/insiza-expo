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
        Schema::table('expos', function (Blueprint $table) {
            $table->string('previous_winner_logo')->nullable()->after('previous_winner_category');
            $table->string('previous_winner_image')->nullable()->after('previous_winner_logo');
        });
    }

    public function down(): void
    {
        Schema::table('expos', function (Blueprint $table) {
            $table->dropColumn(['previous_winner_logo', 'previous_winner_image']);
        });
    }
};
