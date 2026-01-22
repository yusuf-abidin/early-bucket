<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $class = [
            'red'       => 'bg-red-400/10 text-red-400 inset-ring inset-ring-red-400/20',
            'orange'    => 'bg-orange-400/10 text-orange-400 inset-ring inset-ring-orange-400/20',
            'yellow'    => 'bg-yellow-400/10 text-yellow-500 inset-ring inset-ring-yellow-400/30',
            'lime'      => 'bg-lime-400/10 text-lime-500 inset-ring inset-ring-lime-400/30',
            'green'     => 'bg-green-400/10 text-green-400 inset-ring inset-ring-green-500/20',
            'cyan'      => 'bg-cyan-400/10 text-cyan-400 inset-ring inset-ring-cyan-400/20',
            'blue'      => 'bg-blue-400/10 text-blue-400 inset-ring inset-ring-blue-400/30',
            'pink'      => 'bg-pink-400/10 text-pink-400 inset-ring inset-ring-pink-400/20',
            'purple'    => 'bg-purple-400/10 text-purple-400 inset-ring inset-ring-purple-400/30',
            'gray'      => 'bg-gray-50 text-gray-600 inset-ring inset-ring-gray-500/10',
        ];

        foreach ($class as $key => $value) {
            Color::create([
                'name' => $key,
                'class' => $value
            ]);
        }
    }
}
