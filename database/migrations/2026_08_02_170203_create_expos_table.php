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
        Schema::create('expos', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Insiza District Industrial Expo 2026"
            $table->year('year')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('venue');
            $table->text('description')->nullable();
            $table->string('theme')->nullable();
            $table->string('previous_winner')->nullable();
            $table->string('previous_winner_category')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expos');
    }
};
