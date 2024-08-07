<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subject::insert([
            [
                'name' => 'IPA',
                'slug' => 'ipa',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'IPS',
                'slug' => 'ips',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
