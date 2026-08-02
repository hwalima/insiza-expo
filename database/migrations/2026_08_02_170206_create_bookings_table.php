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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expo_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->enum('category', ['mining', 'agriculture', 'education', 'organisations', 'general']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->boolean('payment_verified')->default(false);
            $table->string('payment_reference')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('source')->default('web'); // web | whatsapp
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
