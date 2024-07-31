<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'name' => 'adi',
                'email' => 'adi@adikasi.com',
                'password' => bcrypt('test'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'kasi',
                'email' => 'kasi@adikasi.com',
                'password' => bcrypt('test'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
