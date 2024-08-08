<?php

namespace Tests\Unit\Models;

use App\Models\ClassMember;
use Database\Seeders\ClassMemberSeeder;
use Database\Seeders\ClassRoomSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassMemberTest extends TestCase
{

    public function test_relation(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class, ClassMemberSeeder::class]);

        $memberLinked = ClassMember::where('user_id', '!=', null)->first();
        $this->assertNotNull($memberLinked->classRoom);
        $this->assertNotNull($memberLinked->user);

        $memberAnonim = ClassMember::where('user_id', null)->first();
        $this->assertNotNull($memberAnonim->classRoom);
        $this->assertNull($memberAnonim->user);
    }
}
