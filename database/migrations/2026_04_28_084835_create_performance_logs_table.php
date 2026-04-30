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
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_period_id')->constrained()->onDelete('cascade');

            $table->foreignId('regional_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnDelete();

            $table->boolean('is_achieved')->nullable();
            $table->timestamps();

            $table->unique(['performance_period_id', 'regional_id']);
            $table->unique(['performance_period_id', 'area_id']);
            $table->unique(['performance_period_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
};
