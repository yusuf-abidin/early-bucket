<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PerformanceEtape;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order = 1;
        $categories = [
            'Biasa' => 10,
            'Penting' => 2,
            'Urgent' => 1,
        ];
        foreach ($categories as $name => $colorId) {
            Category::create([
                'name' => $name,
                'type' => 'pending_matter',
                'order' => $order,
                'color_id' => $colorId
            ]);
            $order++;
        }

        $order = 1;
        $categories = [
            'Biasa' => 10,
            'Penting' => 2,
            'Urgent' => 1,
        ];
        foreach ($categories as $name => $colorId) {
            Category::create([
                'name' => $name,
                'type' => 'sifat_memo',
                'order' => $order,
                'color_id' => $colorId
            ]);
            $order++;
        }

        $order = 1;
        $categories = [
            'Tercapai' => 5,
            'Ragu' => 3,
            'Tidak Tercapai' => 1,
        ];

        foreach ($categories as $name => $colorId) {
            Category::create([
                'name' => $name,
                'type' => PerformanceEtape::TYPE_ETAPE_BC,
                'order' => $order,
                'color_id' => $colorId
            ]);
        }

        $order = 1;
        $categories = [
            'Membaik' => 5,
            'Square' => 3,
            'Memburuk' => 1,
        ];

        foreach ($categories as $name => $colorId) {
            Category::create([
                'name' => $name,
                'type' => PerformanceEtape::TYPE_ETAPE_BM,
                'order' => $order,
                'color_id' => $colorId
            ]);
        }

        $order = 1;
        $categories = [
            'Biasa' => 10,
            'Penting' => 2,
            'Urgent' => 1,
        ];
        foreach ($categories as $name => $colorId) {
            Category::create([
                'name' => $name,
                'type' => 'debitur_menabung',
                'order' => $order,
                'color_id' => $colorId
            ]);
            $order++;
        }
    }
}
