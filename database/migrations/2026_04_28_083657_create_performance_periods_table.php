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
            $table->unsignedInteger('order')->default(0);
            $table->string('performance_type', 30);
            $table->tinyInteger('start_date')->nullable();
            $table->tinyInteger('end_date')->nullable();
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
