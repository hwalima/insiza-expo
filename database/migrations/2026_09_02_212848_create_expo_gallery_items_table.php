<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expo_gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expo_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('url');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expo_gallery_items');
    }
};
