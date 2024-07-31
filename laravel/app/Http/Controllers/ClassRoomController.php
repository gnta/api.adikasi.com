<?php

namespace App\Http\Controllers;

use App\Exceptions\ErrorResponse;
use App\Http\Requests\ClassRoom\CreateRequest;
use App\Http\Requests\ClassRoom\UpdateRequest;
use App\Models\ClassRoom;
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

    function update(UpdateRequest $req, $roomId)
    {
        $data = $req->validated();

        $room = $this->findOrFail($roomId);

        $room = ClassRoomService::update(
            room: $room,
            data: $data
        );

        return $this->response(
            data: [
                'id' => $room->id,
                'name' => $room->name
            ]
        );
    }

    function delete($roomId)
    {
        $room = $this->findOrFail($roomId);
        $room->delete();

        return $this->response(
            data: true
        );
    }

    function allMy()
    {
        $paginate = ClassRoom::where('owner_id', Auth::id());

        $paginate = $paginate->paginate();

        return $this->response(
            data: $paginate->items(),
            metadata: [
                'current_page' => $paginate->currentPage(),
                'per_page' => $paginate->perPage(),
                'total_page' => $paginate->lastPage(),
                'total_row' => $paginate->total()
            ]
        );
    }

    private function findOrFail($roomId): ClassRoom
    {
        $room = ClassRoom::where('id', $roomId)->where('owner_id', Auth::id())->first();

        if (is_null($room)) throw new ErrorResponse(
            code: 404,
            message: 'Not found class room'
        );

        return $room;
    }
}
