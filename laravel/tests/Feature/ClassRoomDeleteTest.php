<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use Database\Seeders\ClassRoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Mock\UserMock;
use Tests\TestCase;

class ClassRoomDeleteTest extends TestCase
{
    use UserMock;

    public function test_success(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);

        [$adi, $token] = $this->_adi();
        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $res = $this->deleteJson("/classes/$roomId", [], [
            'Authorization' => "Bearer $token"
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'data' => true
        ]);

        $this->assertNull(ClassRoom::find($roomId));
    }

    public function test_fail_not_found_room(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);

        [$adi, $token] = $this->_adi();
        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id + 10;

        $res = $this->deleteJson("/classes/$roomId", [], [
            'Authorization' => "Bearer $token"
        ]);

        $this->isErrorSafety($res, 404);
    }

    public function test_fail_try_delete_class_other_user(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);

        [$adi] = $this->_adi();
        [, $token] = $this->_kasi();
        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $res = $this->deleteJson("/classes/$roomId", [], [
            'Authorization' => "Bearer $token"
        ]);

        $this->isErrorSafety($res, 404);
    }

    public function test_fail_not_sending_token(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);

        [$adi,] = $this->_adi();
        $room = ClassRoom::where('owner_id', $adi->id)->first();
        $roomId = $room->id;

        $res = $this->deleteJson("/classes/$roomId", [], []);

        $this->isErrorSafety($res, 401);
    }
}
