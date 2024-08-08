<?php

namespace Tests\Unit\Models;

use App\Models\ClassMemberRole;
use Database\Seeders\ClassMemberRoleSeeder;
use Database\Seeders\ClassMemberSeeder;
use Database\Seeders\ClassRoleSeeder;
use Database\Seeders\ClassRoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassMemberRoleTest extends TestCase
{
    public function test_relation(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class, ClassMemberSeeder::class, ClassRoleSeeder::class, ClassMemberRoleSeeder::class]);
        $memberRole = ClassMemberRole::first();

        $this->assertNotNull($memberRole->user);
        $this->assertNotNull($memberRole->classRoom);
        $this->assertNotNull($memberRole->role);
    }
}
