<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [];
        $faker = \Faker\Factory::create();
        foreach (ClassRoom::all() as $class) {
            $students[] = [
                'name' => $faker->word(),
                'class_room_id' => $class->id,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $user = User::where('id', '!=', $class->owner_id)->first();

            $students[] = [
                'name' => $faker->word(),
                'class_room_id' => $class->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        Student::insert($students);
    }
}
