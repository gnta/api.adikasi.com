<?php

namespace Database\Seeders;

use App\Models\ClassRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ClassRole::insert([
            [
                'name' => 'student',
                'slug' => 'student',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'teacher',
                'slug' => 'teacher',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
