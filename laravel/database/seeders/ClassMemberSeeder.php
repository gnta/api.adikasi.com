<?php

namespace Database\Seeders;

use App\Models\ClassMember;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [];
        $faker = \Faker\Factory::create();

        foreach (ClassRoom::all() as $class) {
            $members[] = [
                'name' => $faker->word(),
                'class_room_id' => $class->id,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $user = User::where('id', '!=', $class->owner_id)->first();

            $members[] = [
                'name' => $faker->word(),
                'class_room_id' => $class->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        ClassMember::insert($members);
    }
}
