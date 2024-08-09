<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Database\Seeders\ClassRoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_relation(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class]);

        $user = User::first();

        $this->assertNotNull($user);
        $this->assertEquals(1, $user->classRooms()->count());
    }
}
