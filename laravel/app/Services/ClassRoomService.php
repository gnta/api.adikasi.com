<?php

namespace App\Services;

use App\Exceptions\ErrorResponse;
use App\Models\ClassRoom;
use App\Models\User;

class ClassRoomService
{
    static function create(string $name,  ?User $owner = null): ClassRoom
    {
        $room = new ClassRoom();
        $room->name = $name;

        if (isset($owner)) $room->owner_id = $owner->id;
        $room->save();

        return $room;
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
