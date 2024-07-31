<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use Database\Seeders\ClassRoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Mock\UserMock;
use Tests\TestCase;

class ClassRoomUpdateTest extends TestCase
{

    use UserMock;

    public function test_success_full_data(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$adi, $token] = $this->_adi();
        [$kasi] = $this->_kasi();


        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $payload = [
            'name' => "Update Classses Name",
            'owner_id' => $kasi->id
        ];

        $res = $this->patchJson("/classes/$roomId", $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $res->assertStatus(200);
        $res->assertJsonStructure([
            'data' => [
                'id',
                'name'
            ]
        ]);

        $this->assertEquals($payload['name'], $res->json('data.name'));
        $this->assertEquals($roomId, $res->json('data.id'));

        $newRoom = ClassRoom::find($roomId);

        $this->assertEquals($payload['name'], $newRoom->name);
        $this->assertEquals($payload['owner_id'], $newRoom->owner_id);
    }

    public function test_success_just_name(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$adi, $token] = $this->_adi();

        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $payload = [
            'name' => "Update Classses Name",
        ];

        $res = $this->patchJson("/classes/$roomId", $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $res->assertStatus(200);
        $res->assertJsonStructure([
            'data' => [
                'id',
                'name'
            ]
        ]);

        $this->assertEquals($payload['name'], $res->json('data.name'));
        $this->assertEquals($roomId, $res->json('data.id'));

        $newRoom = ClassRoom::find($roomId);

        $this->assertEquals($payload['name'], $newRoom->name);
        $this->assertEquals($room->owner_id, $newRoom->owner_id);
    }

    public function test_success_just_owner_id(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$adi, $token] = $this->_adi();
        [$kasi] = $this->_kasi();


        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $payload = [
            'owner_id' => $kasi->id
        ];

        $res = $this->patchJson("/classes/$roomId", $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $res->assertStatus(200);
        $res->assertJsonStructure([
            'data' => [
                'id',
                'name'
            ]
        ]);

        $this->assertEquals($room->name, $res->json('data.name'));
        $this->assertEquals($roomId, $res->json('data.id'));

        $newRoom = ClassRoom::find($roomId);

        $this->assertEquals($payload['owner_id'], $newRoom->owner_id);
    }

    public function test_fail_new_owner_id_is_nou_found(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$adi, $token] = $this->_adi();
        [$kasi] = $this->_kasi();


        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $payload = [
            'owner_id' => $kasi->id + 10
        ];

        $res = $this->patchJson("/classes/$roomId", $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $this->isErrorSafety($res, 422);
    }


    public function test_fail_not_sending_data(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$adi, $token] = $this->_adi();

        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $payload = [];

        $res = $this->patchJson("/classes/$roomId", $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $this->isErrorSafety($res, 422);
    }

    public function test_fail_try_to_change_other_user_class_room(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$kasi, $token] = $this->_kasi();
        [$adi] = $this->_adi();


        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $payload = [
            'name' => "Update Classses Name",
            'owner_id' => $kasi->id
        ];

        $res = $this->patchJson("/classes/$roomId", $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $this->isErrorSafety($res, 404);
    }
    public function test_fail_not_sending_token(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$adi] = $this->_adi();
        [$kasi] = $this->_kasi();


        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $payload = [
            'name' => "Update Classses Name",
            'owner_id' => $kasi->id
        ];

        $res = $this->patchJson("/classes/$roomId", $payload, []);

        $this->isErrorSafety($res, 401);
    }
}
