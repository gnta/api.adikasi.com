<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ClassRoomCreateTest extends TestCase
{
    public function test_success(): void
    {
        [$user, $token] = $this->_user();

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
        [$user, $token] = $this->_user();

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


    private function _user()
    {
        $this->seed([UserSeeder::class]);
        $user = User::first();
        $token = Auth::login($user);

        return [$user, $token];
    }
}
