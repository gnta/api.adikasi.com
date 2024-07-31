<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tests\Mock\UserMock;
use Tests\TestCase;

class ClassRoomCreateTest extends TestCase
{
    use UserMock;

    public function test_success(): void
    {
        $this->seed([UserSeeder::class]);
        [$user, $token] = $this->_adi();

        $payload = [
            'name' => 'New class'
        ];

        $res = $this->postJson('/classes', $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $res->assertStatus(200);
        $res->assertJsonStructure([
            'data' => [
                'id',
                'name'
            ]
        ]);

        $this->assertNotNull($res->json('data.id'));
        $this->assertEquals($payload['name'], $res->json('data.name'));

        $room = ClassRoom::first();
        $this->assertEquals($user->id, $room->owner_id);
    }

    public function test_fail_not_sending()
    {
        $this->seed([UserSeeder::class]);
        [$user, $token] = $this->_adi();

        $payload = [];

        $res = $this->postJson('/classes', $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $this->isErrorSafety($res, 422);
    }

    public function test_fail_not_sending_token()
    {
        $payload = ['name' => 'New class'];

        $res = $this->postJson('/classes', $payload, []);

        $this->isErrorSafety($res, 401);
    }
}
