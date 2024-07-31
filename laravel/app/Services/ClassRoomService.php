<?php

namespace App\Services;

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
}
