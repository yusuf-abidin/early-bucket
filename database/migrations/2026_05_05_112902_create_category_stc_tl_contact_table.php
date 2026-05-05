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
        Schema::create('category_stc_tl_contact', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stc_tl_contact_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['stc_tl_contact_id', 'category_id']);
        });

        $categories = [
            'All Bucket' => null,
            'Lancar' => null,
            'Lancar EOM' => null,
            '2A' => null,
            '2A Restruk' => null,
            '2A Restru' => null,
            '2A EOM' => null,
            '2B' => null,
            '2B EOM' => null,
            '2C' => null,
            'Restruk' => null,
            'KL' => null,
            'DIR' => null,
            'Macet' => null,
            'RAS' => null,
            '2C EOM' => null,
            'NPL' => null,
            'Collective' => null,
            'DPK Restru' => null,
        ];
        $order = 1;
        foreach ($categories as $name => $colorId) {
            \App\Models\Category::create([
                'name' => $name,
                'type' => 'bucket',
                'order' => $order,
                'color_id' => $colorId,
            ]);
            $order++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_stc_tl_contact');
    }
};
