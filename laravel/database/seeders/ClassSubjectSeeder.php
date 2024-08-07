<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\ClassSubject;
use App\Models\User;
use App\Services\SubjectService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassSubjectSeeder extends Seeder
{

    public function run(): void
    {
        $subjects = [];
        $faker = \Faker\Factory::create();
        foreach (ClassRoom::all() as $class) {
            $subjects[] = [
                'subject_id' => SubjectService::get('IPS')->id,
                'class_room_id' => $class->id,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $user = User::where('id', '!=', $class->owner_id)->first();

            $subjects[] = [
                'subject_id' => SubjectService::get('IPA')->id,
                'class_room_id' => $class->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        ClassSubject::insert($subjects);
    }
}
