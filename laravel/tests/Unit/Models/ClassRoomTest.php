<?php

namespace Tests\Unit\Models;

use App\Models\ClassRoom;
use App\Services\ClassRoomService;
use Database\Seeders\ClassRoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassRoomTest extends TestCase
{
    public function test_relation(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);

        $room = ClassRoom::first();

        $this->assertNotNull($room);
        $this->assertNotNull($room->owner);
    }

    public function test_relation_user_null()
    {
        $room = ClassRoomService::create('Missing owner');

        $this->assertNotNull($room->id);
        $this->assertNull($room->owner);
    }
}
