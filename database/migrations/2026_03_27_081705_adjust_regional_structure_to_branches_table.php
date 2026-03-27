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
        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->change();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('regional_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

        });

        $regionalId = DB::table('regionals')->value('id');
        DB::table('branches')->update([
            'regional_id' => $regionalId
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['regional_id']);
            $table->dropColumn('regional_id');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable(false)->change();
        });
    }
};
