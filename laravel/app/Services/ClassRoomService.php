<?php

namespace App\Services;

use App\Exceptions\ErrorResponse;
use App\Models\ClassMember;
use App\Models\ClassMemberRole;
use App\Models\ClassRoom;
use App\Models\ClassSubject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassRoomService
{
    static function create(string $name,  ?User $owner = null, $students = [], $subjects = []): ClassRoom
    {
        try {
            DB::beginTransaction();

            $room = new ClassRoom();
            $room->name = $name;

            if (isset($owner)) $room->owner_id = $owner->id;
            $room->save();

            if (!empty($students)) {
                $members = [];
                $roles = [];

                $students = collect($students);
                $usersEmail = $students->pluck('email');
                $roleStudent = ClassRoleService::get('student');
                $currentTime = now();
                $DBusers = User::whereIn('email', $usersEmail)->get()->keyBy('email');

                foreach ($students as $student) {
                    if (isset($student['name'])) {
                        $email = $student['email'] ?? null;
                        $userId = null;


                        if ($email) $userId = $DBusers[$email]->id;

                        $members[] = [
                            'class_room_id' => $room->id,
                            'name' => $student['name'],
                            'user_id' => $userId,
                            'created_at' => $currentTime,
                            'updated_at' => $currentTime
                        ];

                        $roles[] = [
                            'class_room_id' => $room->id,
                            'user_id' => $userId,
                            'class_role_id' => $roleStudent->id
                        ];
                    }
                }

                if (!empty($members)) ClassMember::insert($members);
                if (!empty($roles)) ClassMemberRole::insert($roles);
            }

            if (!empty($subjects)) {

                $subjects = collect($subjects);
                $usersEmail = $subjects->pluck('teacherEmail');
                $DBusers = User::whereIn('email', $usersEmail)->get()->keyBy('email');
                $roles = $members = $newSubjects = [];
                $roleTeacher = ClassRoleService::get('teacher');
                $currentTime = now();

                foreach ($subjects as $subject) {
                    $userId = null;
                    $teacherName = $subject['teacherName'] ?? null;
                    $teacherEmail = $subject['teacherEmail'] ?? null;
                    if ($teacherEmail) $userId = $DBusers[$teacherEmail]->id;

                    $newSubjects[] = [
                        'subject_id' => SubjectService::get($subject['name'])->id,
                        'class_room_id' => $room->id,
                        'user_id' => $userId,
                        'created_at' => $currentTime,
                        'updated_at' => $currentTime
                    ];

                    if ($teacherName) {
                        $members[] = [
                            'class_room_id' => $room->id,
                            'name' => $teacherName,
                            'user_id' => $userId,
                            'created_at' => $currentTime,
                            'updated_at' => $currentTime
                        ];

                        $roles[] = [
                            'class_room_id' => $room->id,
                            'user_id' => $userId,
                            'class_role_id' => $roleTeacher->id
                        ];
                    }
                }

                if (!empty($newSubjects)) ClassSubject::insert($newSubjects);
                if (!empty($members)) ClassMember::insert($members);
                if (!empty($roles)) ClassMemberRole::insert($roles);
            }

            DB::commit();
            return $room;
        } catch (\Exception  $err) {
            DB::rollBack();

            if ($err instanceof ErrorResponse) throw $err;

            throw new ErrorResponse(message: $err->getMessage(), code: 500);
        }
    }

    static function update(ClassRoom $room, $data = [])
    {
        $updatedData  = [];
        $fillable = $room->getFillable();

        foreach ($data as $key => $value) {
            if (in_array($key, $fillable)) {
                $updatedData[$key] = $value;
            }
        }

        if (empty($data)) throw new ErrorResponse(
            code: 422,
            message: 'Not data sended to update'
        );

        foreach ($updatedData as $key => $value) {
            switch ($key) {
                case 'owner_id':
                    $owner = User::where('id', $value)->first();

                    if (is_null($owner)) throw new ErrorResponse(
                        code: 422,
                        message: 'Not found user'
                    );
                    $room->$key = $value;
                    break;

                default:
                    $room->$key = $value;
                    break;
            }
        }

        $room->save();

        return $room;
    }
}
