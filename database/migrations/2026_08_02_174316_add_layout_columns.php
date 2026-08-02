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
        Schema::table('stands', function (Blueprint $table) {
            $table->boolean('is_placed')->default(false)->after('section');
        });

        Schema::table('expos', function (Blueprint $table) {
            $table->string('floor_plan_image')->nullable()->after('is_active');
            $table->boolean('is_layout_published')->default(false)->after('floor_plan_image');
        });
    }

    public function down(): void
    {
        Schema::table('stands', fn($t) => $t->dropColumn('is_placed'));
        Schema::table('expos',  fn($t) => $t->dropColumn(['floor_plan_image', 'is_layout_published']));
    }
};
