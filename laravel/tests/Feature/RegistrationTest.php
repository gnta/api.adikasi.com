<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

class RegistrationTest extends TestCase
{
    protected $payload = [
        'name' => 'Adikasi System Testing',
        'email' => 'test@adikasi.com',
        'password' => 'test',
        'password_confirmation' => 'test'
    ];

    public function test_success(): void
    {

        $res = $this->postJson('/register', $this->payload);
        $res->assertStatus(200);
        $res->assertJsonStructure([
            'data' => [
                'name',
                'email',
                'token'
            ]
        ]);

        $this->assertNotNull($res->json('data.name'));
        $this->assertNotNull($res->json('data.email'));
        $this->assertNotNull($res->json('data.token'));
        $this->assertNull($res->json('data.password'));

        $this->assertEquals(1, User::count());
        $user = User::first();

        $this->assertEquals($this->payload['name'], $user->name);
        $this->assertEquals($this->payload['email'], $user->email);
        $this->assertTrue(Hash::check($this->payload['password'], $user->password));
    }

    public function test_fail_password_not_match()
    {
        $this->payload['password_confirmation'] = 'beda';

        $res = $this->postJson('/register', $this->payload);
        $this->isErrorSafety($res, 422);
    }

    public function test_fail_invalid_email()
    {
        $this->payload['password_confirmation'] = 'adikasi.com';

        $res = $this->postJson('/register', $this->payload);
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

    public function test_email_url_based_on_host_requestor()
    {
        Notification::fake();
        Notification::assertNothingSent();

        Mail::fake();
        $faker = \Faker\Factory::create();
        $domain = $faker->domainName();

        $request = Request::create('/register', 'POST', [], [], [], [
            'HTTP_HOST' => $domain,
            'HTTPS' => 'on',
            'SERVER_NAME' => $domain,
            'SERVER_PORT' => '443',
            'REQUEST_URI' => '/register',
        ], json_encode($this->payload));


        $request->headers->set('Content-Type', 'application/json');

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $user = User::first();

        Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user, $domain) {
            $mail = $notification->toMail($user);

            $isRightLinkUrl = str_contains(
                $mail->actionUrl,
                "https://$domain/email/verify"
            );

            return $isRightLinkUrl;
        });
    }
}
