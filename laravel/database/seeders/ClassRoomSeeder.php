<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [];
        $faker = \Faker\Factory::create();
        foreach (User::all() as $user) {
            $rooms[] = [
                'name' => $faker->word(),
                'owner_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        ClassRoom::insert($rooms);
    }
}
