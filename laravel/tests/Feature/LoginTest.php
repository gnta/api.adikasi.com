<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_success(): void
    {
        $this->seed([UserSeeder::class]);

        $response = $this->postJson('/login', [
            'email' => 'test@adikasi.com',
            'password' => 'test'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'name',
                'email',
                'token',
            ]
        ]);

        $this->assertNotNull($response->json('data.name'));
        $this->assertNotNull($response->json('data.email'));
        $this->assertNotNull($response->json('data.token'));
    }

    public function test_wrong_email(): void
    {
        $this->seed([UserSeeder::class]);

        $response = $this->postJson('/login', [
            'email' => 'userilang@adikasi.com',
            'password' => 'test'
        ]);

        $this->isErrorSafety($response, 422);
    }

    public function test_wrong_password(): void
    {
        $this->seed([UserSeeder::class]);

        $response = $this->postJson('/login', [
            'email' => 'test@adikasi.com',
            'password' => 'salah'
        ]);

        $this->isErrorSafety($response, 422);
    }
}
