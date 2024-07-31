<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassRoom\CreateRequest;
use App\Services\ClassRoomService;
use App\Traits\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassRoomController extends Controller
{
    use Response;

    function create(CreateRequest $req)
    {
        $data = $req->validated();

        $room = ClassRoomService::create(
            name: $data['name'],
            owner: Auth::user()
        );

        return $this->response(
            data: [
                'id' => $room->id,
                'name' => $room->name
            ]
        );
    }
}
