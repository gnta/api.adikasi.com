<?php

namespace Tests\Unit\Models;

use App\Models\ClassSubject;
use Database\Seeders\ClassRoomSeeder;
use Database\Seeders\ClassSubjectSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassSubjectTest extends TestCase
{
    function test_relation(): void
    {
        $this->seed([UserSeeder::class, ClassRoomSeeder::class, SubjectSeeder::class, ClassSubjectSeeder::class]);

        $subjectLinkedUser = ClassSubject::where('user_id', '!=', null)->first();
        $this->assertNotNull($subjectLinkedUser->classRoom);
        $this->assertNotNull($subjectLinkedUser->user);
        $this->assertNotNull($subjectLinkedUser->subject);


        $subjectNotLinkedUser = ClassSubject::where('user_id',  null)->first();
        $this->assertNotNull($subjectNotLinkedUser->classRoom);
        $this->assertNull($subjectNotLinkedUser->user);
        $this->assertNotNull($subjectNotLinkedUser->subject);
    }
}
