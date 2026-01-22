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
        Schema::create('memos', function (Blueprint $table) {
            $table->id();
            $table->dateTime('received_at')->nullable();
            $table->text('origin')->nullable();
            $table->text('reference_number')->nullable();
            $table->text('subject')->nullable();
            $table->foreignId('category_id')->constrained();
            $table->string('document_link', 2048)->nullable();
            $table->dateTime('completed_at')->nullable()->default(null);
            $table->dateTime('due_date')->nullable();
            $table->text('follow_up_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memos');
    }
};
