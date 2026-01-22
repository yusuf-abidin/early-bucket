<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            'Pak Juli'  => 10,
            'Shinta'    => 2,
            'Marsha'    => 1,
            'Doni'      => 3,
        ];
        foreach ($users as $name => $colorId) {
            User::create([
                'name' => $name,
                'email' => strtolower(Str::slug($name)) . '@gmail.com',
                'password' => Hash::make('password'),
                'position' => ($name === 'Pak Juli') ? 'Kepala Departemen' : 'Staff',
                'role' => ($name === 'Pak Juli') ? 'admin' : 'user',
                'color_id' => $colorId
            ]);
        }

        User::create([
            'name' => 'Yusuf Abidin',
            'email' => 'yusufabidin02@gmail.com',
            'password' => Hash::make('password'),
            'position' => 'Staff',
            'role' => 'admin',
            'color_id' => 8
        ]);
    }
}
