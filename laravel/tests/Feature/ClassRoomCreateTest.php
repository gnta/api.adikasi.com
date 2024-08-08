<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\ClassSubject;
use App\Models\Subject;
use App\Services\SubjectService;
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

        $students = ClassRoom::find($res->json('data.id'))->getMemberByRole('student');

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
        $students = ClassRoom::find($res->json('data.id'))->getMemberByRole('student');
        $this->assertEquals(count($payload['students']), count($students));

        for ($i = 0; $i < count($students); $i++) {
            $payloadStudent = $payload['students'][$i];
            $this->assertEquals($payloadStudent['name'], $students[$i]->name);

            if (isset($payloadStudent['email'])) {
                $this->assertEquals($kasi->id, $students[$i]->user_id);
            }
        }
    }

    public function test_success_with_just_subjects()
    {
        $this->seed([UserSeeder::class]);
        [, $token] = $this->_adi();
        $oldSubjectTotal = Subject::count();
        $payload = [
            'name' => 'New class',
            'subjects' => [
                ['name' => 'Fisika'],
                ['name' => 'Kimia'],
            ]
        ];

        $res = $this->postJson('/classes', $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $res->assertStatus(200);
        $newSubjectTotal =  Subject::count();
        $payloadSubject = collect($payload['subjects']);
        $payloadSubjectTotal = $payloadSubject->count();
        $classSubjects = ClassSubject::where('class_room_id', $res->json('data.id'))->get();

        $payloadSubjectNames = $payloadSubject->pluck('name')->toArray();

        foreach ($payload['subjects'] as $ps) {
            $this->assertNotNull(SubjectService::get($ps['name']));
        }


        foreach ($classSubjects as $cs) {
            $sb = Subject::find($cs->subject_id);
            $this->assertNotNull($sb);
            $this->assertTrue(in_array($sb->name, $payloadSubjectNames));
            $this->assertNull($cs->user_id);
        }

        $this->assertEquals($oldSubjectTotal + $payloadSubjectTotal, $newSubjectTotal);
        $this->assertEquals($payloadSubjectTotal, count($classSubjects));
    }

    public function test_success_with_subjects_with_anonim_teacher()
    {
        $this->seed([UserSeeder::class]);
        [, $token] = $this->_adi();

        $payload = [
            'name' => 'New class',
            'subjects' => [
                ['name' => 'Fisika', 'teacherName' => "andre"],
                ['name' => 'Kimia', 'teacherName' => 'agus'],
            ]
        ];

        $res = $this->postJson('/classes', $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $res->assertStatus(200);

        $teacher = ClassRoom::find($res->json('data.id'))->getMemberByRole('teacher');

        $this->assertEquals(count($payload['subjects']), count($teacher));

        for ($i = 0; $i < count($teacher); $i++) {
            $this->assertEquals($payload['subjects'][$i]['teacherName'], $teacher[$i]->name);
        }
    }

    public function test_success_with_subjects_with_linked_teacher()
    {
        $this->seed([UserSeeder::class]);
        [$kasi, $token] = $this->_kasi();
        [, $token] = $this->_adi();

        $payload = [
            'name' => 'New class',
            'subjects' => [
                ['name' => 'Fisika', 'teacherName' => "andre"],
                ['name' => 'Kimia', 'teacherName' => 'agus'],
                ['name' => 'IPA', 'teacherName' => $kasi->name, 'teacherEmail' => $kasi->email],
            ]
        ];

        $res = $this->postJson('/classes', $payload, [
            'Authorization' => "Bearer $token"
        ]);

        $teacher = ClassRoom::find($res->json('data.id'))->getMemberByRole('teacher');

        for ($i = 0; $i < count($teacher); $i++) {
            $payloadActive = $payload['subjects'][$i];
            $this->assertEquals($payloadActive['teacherName'], $teacher[$i]->name);

            Log::info(json_encode($teacher[$i]));
            if (isset($payloadActive['teacherEmail'])) {
                $this->assertEquals($kasi->id, $teacher[$i]->user_id);
            }
        }
    }
}
