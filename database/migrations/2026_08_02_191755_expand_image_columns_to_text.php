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
        // Expand all image/URL columns to TEXT so any-length URLs can be stored
        Schema::table('sponsors', fn($t) => $t->text('logo')->nullable()->change());
        Schema::table('guest_of_honors', fn($t) => $t->text('photo')->nullable()->change());
        Schema::table('expos', function (Blueprint $table) {
            $table->text('floor_plan_image')->nullable()->change();
            $table->text('previous_winner_logo')->nullable()->change();
            $table->text('previous_winner_image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sponsors', fn($t) => $t->string('logo')->nullable()->change());
        Schema::table('guest_of_honors', fn($t) => $t->string('photo')->nullable()->change());
        Schema::table('expos', function (Blueprint $table) {
            $table->string('floor_plan_image')->nullable()->change();
            $table->string('previous_winner_logo')->nullable()->change();
            $table->string('previous_winner_image')->nullable()->change();
        });
    }
};
