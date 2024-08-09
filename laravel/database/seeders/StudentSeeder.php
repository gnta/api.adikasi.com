<?php

namespace Database\Seeders;

use App\Models\ClassMember;
use App\Models\ClassMemberRole;
use App\Models\ClassRoom;
use App\Models\User;
use App\Services\ClassRoleService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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

        $roles = [];
        $roleTeacher = ClassRoleService::get('student');

        foreach (ClassMember::where('user_id', '!=', null)->get() as $member) {
            $roles[] = [
                'class_room_id' => $member->class_room_id,
                'user_id' => $member->user_id,
                'class_role_id' => $roleTeacher->id
            ];
        }

        ClassMemberRole::insert($roles);
    }
}
