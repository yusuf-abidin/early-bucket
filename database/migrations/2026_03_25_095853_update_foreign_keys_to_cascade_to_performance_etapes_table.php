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
        Schema::table('performance_etapes', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_etapes', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }
};
