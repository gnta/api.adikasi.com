<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_success(): void
    {
        $payload = [
            'name' => 'Adikasi System Testing',
            'email' => 'test@adikasi.com',
            'password' => 'test',
            'password_confirmation' => 'test'
        ];

        $res = $this->postJson('/register', $payload);
        $res->assertStatus(200);
        $res->assertJsonStructure([
            'data' => [
                'name',
                'email'
            ]
        ]);

        $this->assertNull($res->json('data.password'));

        $this->assertEquals(1, User::count());
        $user = User::first();

        $this->assertEquals($payload['name'], $user->name);
        $this->assertEquals($payload['email'], $user->email);
        $this->assertTrue(Hash::check($payload['password'], $user->password));
    }

    public function test_fail_password_not_match()
    {
        $payload = [
            'name' => 'Adikasi System Testing',
            'email' => 'test@adikasi.com',
            'password' => 'test',
            'password_confirmation' => 'beda'
        ];

        $res = $this->postJson('/register', $payload);
        $this->isErrorSafety($res, 422);
    }

    public function test_fail_invalid_email()
    {
        $payload = [
            'name' => 'Adikasi System Testing',
            'email' => 'adikasi.com',
            'password' => 'test',
            'password_confirmation' => 'test'
        ];

        $res = $this->postJson('/register', $payload);
        $this->isErrorSafety($res, 422);
    }

    public function test_fail_email_already_register()
    {
        $this->seed([UserSeeder::class]);

        $payload = [
            'name' => 'Adikasi System Testing',
            'email' => 'test@adikasi.com',
            'password' => 'test',
            'password_confirmation' => 'test'
        ];

        $res = $this->postJson('/register', $payload);
        $this->isErrorSafety($res, 422);
    }



    private function isErrorSafety(\Illuminate\Testing\TestResponse $res, $errorStatus)
    {
        $res->assertStatus($errorStatus);
        $this->assertNotNull($res->json('error.message'));
    }
}
