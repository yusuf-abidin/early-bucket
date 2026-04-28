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
        Schema::create('performance_periods', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('month');
            $table->year('year');
            $table->enum('performance_type', ['etape_1', 'etape_2', 'etape_3', 'eom']);
            $table->tinyInteger('start_date');
            $table->tinyInteger('end_date');
            $table->timestamps();

            $table->unique(['month', 'year', 'performance_type'], 'idx_unique_periods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_periods');
    }
};
