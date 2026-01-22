<?php

namespace Database\Seeders;

use App\Models\Category;
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
                'type' => 'memo',
                'order' => $order,
                'color_id' => $colorId
            ]);
            $order++;
        }
    }
}
