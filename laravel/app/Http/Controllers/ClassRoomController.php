<?php

namespace App\Http\Controllers;

use App\Exceptions\ErrorResponse;
use App\Http\Requests\ClassRoom\CreateRequest;
use App\Http\Requests\ClassRoom\UpdateRequest;
use App\Models\ClassMember;
use App\Models\ClassMemberRole;
use App\Models\ClassRoom;
use App\Models\ClassSubject;
use App\Services\ClassRoomService;
use App\Traits\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClassRoomController extends Controller
{
    use Response;

    function create(CreateRequest $req)
    {
        $data = $req->validated();

        $room = ClassRoomService::create(
            name: $data['name'],
            owner: Auth::user(),
            students: $data['students'] ?? [],
            subjects: $data['subjects'] ?? []
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
        try {
            DB::beginTransaction();
            $room = $this->findOrFail($roomId);

            ClassMember::where('class_room_id', $roomId)->delete();
            ClassMemberRole::where('class_room_id', $roomId)->delete();
            ClassSubject::where('class_room_id', $roomId)->delete();

            $room->delete();

            DB::commit();
            return $this->response(
                data: true
            );
        } catch (\Exception $err) {
            DB::rollBack();

            if ($err instanceof ErrorResponse) throw $err;

            throw new ErrorResponse(message: $err->getMessage(), code: 500);
        }
    }

    function allMy()
    {
        $userId = Auth::id();
        $paginate = ClassRoom::where('owner_id', $userId)
            ->union(ClassMember::select('class_rooms.*')
                ->where('user_id', $userId)
                ->join('class_rooms', 'class_members.class_room_id', '=', 'class_room_id'));

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
