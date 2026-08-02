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
        Schema::create('stands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expo_id')->constrained()->cascadeOnDelete();
            $table->string('stand_number'); // e.g. A1, B3
            $table->enum('size', ['6x3', '3x3'])->default('3x3'); // metres
            $table->enum('category', ['mining', 'agriculture', 'education', 'organisations', 'general'])->default('general');
            $table->enum('status', ['available', 'reserved', 'occupied'])->default('available');
            $table->decimal('price', 10, 2)->default(0);
            // Layout / floor-plan positioning (admin-editable)
            $table->integer('grid_x')->default(0); // column on floor plan grid
            $table->integer('grid_y')->default(0); // row on floor plan grid
            $table->integer('grid_w')->default(1); // width in grid units
            $table->integer('grid_h')->default(1); // height in grid units
            $table->integer('rotation')->default(0); // 0 or 90 degrees
            $table->string('section')->nullable(); // Hall A, Hall B, Outdoor, etc.
            $table->string('exhibitor_name')->nullable();
            $table->string('exhibitor_logo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stands');
    }
};
