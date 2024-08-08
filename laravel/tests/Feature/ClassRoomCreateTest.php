<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\Mock\UserMock;
use Tests\TestCase;

class ClassRoomCreateTest extends TestCase
{
    use UserMock;

    public function test_success_just_name(): void
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

    public function test_success_with_student_anonim()
    {
        $this->seed([UserSeeder::class]);
        [, $token] = $this->_adi();

        $payload = [
            'name' => 'New class',
            'students' => [
                ['name' => 'Andi'],
                ['name' => 'Budi'],
            ]
        ];

        $res = $this->postJson('/classes', $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $res->assertStatus(200);

        $students = ClassRoom::find($res->json('data.id'))->students();

        $this->assertEquals(count($payload['students']), count($students));

        for ($i = 0; $i < count($students); $i++) {
            $this->assertEquals($payload['students'][$i]['name'], $students[$i]->name);
        }
    }

    public function test_success_with_student_linked()
    {
        $this->seed([UserSeeder::class]);
        [, $token] = $this->_adi();
        [$kasi, $token] = $this->_kasi();

        $payload = [
            'name' => 'New class',
            'students' => [
                ['name' => 'Andi'],
                ['name' => 'Budi'],
                [
                    'name' => $kasi->name,
                    'email' => $kasi->email
                ],
            ]
        ];

        $res = $this->postJson('/classes', $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $res->assertStatus(200);
        $students = ClassRoom::find($res->json('data.id'))->students();
        $this->assertEquals(count($payload['students']), count($students));

        for ($i = 0; $i < count($students); $i++) {
            $payloadStudent = $payload['students'][$i];
            $this->assertEquals($payloadStudent['name'], $students[$i]->name);

            if (isset($payloadStudent['email'])) {
                $this->assertEquals($kasi->id, $students[$i]->user_id);
            }
        }
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
