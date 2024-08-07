<?php

namespace App\Services;

use App\Exceptions\ErrorResponse;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClassRoomService
{
    static function create(string $name,  ?User $owner = null, $students = []): ClassRoom
    {
        try {
            DB::beginTransaction();

            $room = new ClassRoom();
            $room->name = $name;

            if (isset($owner)) $room->owner_id = $owner->id;
            $room->save();

            if (!empty($students)) {
                $batch = [];

                $students = collect($students);
                $usersEmail = $students->pluck('email');

                $DBusers = User::whereIn('email', $usersEmail)->get()->keyBy('email');

                foreach ($students as $student) {
                    if (isset($student['name'])) {
                        $email = $student['email'] ?? null;
                        $userId = null;
                        $currentTime = now();

                        if ($email) {
                            $userId = $DBusers[$email]->id;
                        }


                        $batch[] = [
                            'class_room_id' => $room->id,
                            'name' => $student['name'],
                            'user_id' => $userId,
                            'created_at' => $currentTime,
                            'updated_at' => $currentTime
                        ];
                    }
                }

                if (!empty($batch)) Student::insert($batch);
            }

            DB::commit();
            return $room;
        } catch (\Exception  $err) {
            DB::rollBack();

            if ($err instanceof ErrorResponse) {
                throw $err;
            }

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
