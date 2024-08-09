<?php

namespace Database\Seeders;

use App\Models\ClassMember;
use App\Models\ClassMemberRole;
use App\Models\ClassRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassMemberRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [];
        $roleTeacher = ClassRole::where('slug', 'teacher')->first();

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
