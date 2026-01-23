<?php

namespace Database\Seeders;

use App\Models\Etape;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EtapeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $etapes = [
            ['name' => 'ETAPE 1'],
            ['name' => 'ETAPE 2'],
            ['name' => 'ETAPE 3'],
        ];

        Etape::insert($etapes);
    }
}
