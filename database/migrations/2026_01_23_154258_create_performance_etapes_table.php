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
        Schema::create('performance_etapes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->tinyInteger('etape_no');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('komitmen_etape_bc_id')->nullable()->constrained('categories', 'id');
            $table->foreignId('komitmen_etape_bm_id')->nullable()->constrained('categories', 'id');
            $table->decimal('prognosa_akhir_bulan')->nullable();
            $table->text('kendala')->nullable();
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->timestamps();

            $table->unique(['branch_id', 'year', 'month', 'etape_no'], 'unique_performance_etapes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_etapes');
    }
};
