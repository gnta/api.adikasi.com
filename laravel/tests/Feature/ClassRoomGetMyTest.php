<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use Database\Seeders\ClassRoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Mock\UserMock;
use Tests\TestCase;

class ClassRoomGetMyTest extends TestCase
{
    use UserMock;

    private $stucure = [
        'data' => [
            '*' => [
                'id',
                'name',
                'created_at',
                'updated_at',
                'owner_id'
            ]
        ],
        'metadata' => [
            'current_page',
            'per_page',
            'total_page',
            'total_row',
        ]
    ];

    public function test_success(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$adi, $token] = $this->_adi();

        $res = $this->get('/my/classes', [
            'Authorization' => "Bearer $token"
        ]);
        $res->assertStatus(200);
        $res->assertJsonStructure($this->stucure);

        $room = $res->json('data');

        foreach ($res->json('data') as $room) {
            $this->assertEquals($adi->id, $room['owner_id']);
        }

        $this->assertEquals(1, $res->json('metadata.current_page'));
        $this->assertEquals(ClassRoom::where('owner_id', $adi->id)->count(), $res->json('metadata.current_page'));
    }

    public function test_success_page_2(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$adi, $token] = $this->_adi();

        $res = $this->get('/my/classes?page=2', [
            'Authorization' => "Bearer $token"
        ]);
        $res->assertStatus(200);
        $res->assertJsonStructure($this->stucure);
        $this->assertEquals(2, $res->json('metadata.current_page'));
    }

    public function test_fail_not_sending_token()
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);
        [$adi, $token] = $this->_adi();

        $res = $this->get('/my/classes?page=2', []);

        $this->isErrorSafety($res, 401);
    }
}
