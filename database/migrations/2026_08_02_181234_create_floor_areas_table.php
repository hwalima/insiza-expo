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
        Schema::create('floor_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expo_id')->constrained()->cascadeOnDelete();
            $table->string('label', 100);
            $table->string('type', 50)->default('other'); // stage, tent, entrance, registration, parking, vip, other
            $table->string('bg_color', 20)->default('#374151');
            $table->string('text_color', 20)->default('#ffffff');
            $table->integer('grid_x')->default(0);
            $table->integer('grid_y')->default(0);
            $table->integer('grid_w')->default(2);
            $table->integer('grid_h')->default(2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floor_areas');
    }
};
