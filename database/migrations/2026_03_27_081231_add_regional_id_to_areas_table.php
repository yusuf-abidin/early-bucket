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
        Schema::table('areas', function (Blueprint $table) {
            $table->foreignId('regional_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
        $regional = DB::table('regionals')->select()->first();
        DB::table('areas')->update(['regional_id' => $regional->id]);

        Schema::table('areas', function (Blueprint $table) {
            $table->foreignId('regional_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['regional_id']);
            $table->dropColumn('regional_id');
        });
    }
};
