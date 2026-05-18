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
        Schema::create('consumer_recaps', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('date');
            $table->unsignedTinyInteger('month');
            $table->year('year');
            $table->bigInteger('consumer')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['year', 'month', 'date'], 'unique_consumer_recaps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumer_recaps');
    }
};
