<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Google_Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\TestCase;

class LoginGoogleTest extends TestCase
{
    use ProphecyTrait;

    public function test_success_create_and_login(): void
    {
        $expectedPayload = [
            "name" => "username test",
            "email" => "test@gmail.com",
            "picture" => 'https://example.com/image.png',
        ];

        $googleClientMock = $this->prophesize(Google_Client::class);

        $googleClientMock->verifyIdToken('test')->willReturn($expectedPayload);

        $this->app->instance(Google_Client::class, $googleClientMock->reveal());

        $payload = [
            "token" => 'test',
        ];

        $res = $this->postJson("/login/google", $payload);

        $res->assertStatus(200);
        $res->assertJsonStructure([
            'data' => [
                'name',
                'email',
                'token',
            ]
        ]);

        $this->assertNotNull($res->json('data.name'));
        $this->assertNotNull($res->json('data.email'));
        $this->assertNotNull($res->json('data.token'));

        $this->assertEquals($expectedPayload['name'], $res->json('data.name'));
        $this->assertEquals($expectedPayload['email'], $res->json('data.email'));

        $this->assertEquals(1, User::count());
    }

    public function test_success_login_and_not_create_a_new_user(): void
    {
        $this->seed([UserSeeder::class]);

        $totalUser = User::count();

        $user = User::first();

        $expectedPayload = [
            "name" => $user->name,
            "email" => $user->email,
            "picture" => 'https://example.com/image.png',
        ];

        $googleClientMock = $this->prophesize(Google_Client::class);

        $googleClientMock->verifyIdToken('test')->willReturn($expectedPayload);

        $this->app->instance(Google_Client::class, $googleClientMock->reveal());

        $payload = [
            "token" => 'test',
        ];

        $res = $this->postJson("/login/google", $payload);

        $res->assertStatus(200);
        $res->assertJsonStructure([
            'data' => [
                'name',
                'email',
                'token',
            ]
        ]);

        $this->assertNotNull($res->json('data.name'));
        $this->assertNotNull($res->json('data.email'));
        $this->assertNotNull($res->json('data.token'));

        $this->assertEquals($expectedPayload['name'], $res->json('data.name'));
        $this->assertEquals($expectedPayload['email'], $res->json('data.email'));

        $this->assertEquals($totalUser, User::count());
    }

    public function test_fail_not_sending_data()
    {
        $payload = [];
        $res = $this->postJson("/login/google", $payload);
        $this->isErrorSafety($res, 422);
    }

    public function test_fail_fail_decode(): void
    {

        $googleClientMock = $this->prophesize(Google_Client::class);
        $googleClientMock->verifyIdToken('test')->willReturn(false);

        $this->app->instance(Google_Client::class, $googleClientMock->reveal());

        $payload = [
            "token" => 'test',
        ];

        $res = $this->postJson("/login/google", $payload);
        $this->isErrorSafety($res, 422);
    }
}
