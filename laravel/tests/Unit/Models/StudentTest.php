<?php

namespace Tests\Unit\Models;

use App\Models\Student;
use Database\Seeders\ClassRoomSeeder;
use Database\Seeders\StudentSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class StudentTest extends TestCase
{
    public function test_relation(): void
    {
        $this->seed([
            UserSeeder::class,
            ClassRoomSeeder::class,
            StudentSeeder::class
        ]);

        $studentLinked = Student::where('user_id', '!=', null)->first();
        $this->assertNotNull($studentLinked->classRoom);
        $this->assertNotNull($studentLinked->user);

        $studentAnonim = Student::where('user_id', null)->first();
        $this->assertNotNull($studentAnonim->classRoom);
        $this->assertNull($studentAnonim->user);
    }
}
