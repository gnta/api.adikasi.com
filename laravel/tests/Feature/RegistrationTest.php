<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class RegistrationTest extends TestCase
{
    protected $payload = [];
    function setUp(): void
    {
        parent::setUp();

        $this->payload = [
            'name' => 'Adikasi System Testing',
            'email' => 'test@adikasi.com',
            'password' => 'test',
            'password_confirmation' => 'test'
        ];
    }

    public function test_success(): void
    {
        Mail::fake();
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
        $this->payload['email'] = 'adikasi.com';

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

    public function test_verify_registration_success()
    {
        $this->seed([UserSeeder::class]);

        Notification::fake();
        Mail::fake();

        $user = User::first();
        $notification = new VerifyEmail();
        $user->notify($notification);

        $sentNotification = Notification::sent($user, VerifyEmail::class)->first();

        $mail = $sentNotification->toMail($user);

        $tempUrl  = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $this->assertEquals($mail->actionUrl, $tempUrl);

        $res = $this->getJson($mail->actionUrl);

        $res->assertStatus(200);
        $res->assertJson([
            'data' => true
        ]);

        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_verify_registration_fail_expire_link()
    {
        $this->seed([UserSeeder::class]);

        $user = User::first();

        $tempUrl  = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->subMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $res = $this->getJson($tempUrl);
        $this->isErrorSafety($res, 253);
    }

    public function test_verify_registration_fail_invalid_id()
    {
        $this->seed([UserSeeder::class]);

        $user = User::first();

        $tempUrl  = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey() + 100,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $res = $this->getJson($tempUrl);
        $this->isErrorSafety($res, 253);
    }

    public function test_verify_registration_fail_invalid_hash()
    {
        $this->seed([UserSeeder::class]);

        $user = User::first();

        $validHash = sha1($user->getEmailForVerification());

        $validUrl  = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => $validHash,
            ]
        );

        $validUrl = str_replace($validHash, $validHash . 'sakldalsdas', $validUrl);
        $res = $this->getJson($validUrl);
        $this->isErrorSafety($res, 253);
    }

    public function test_verify_registration_fail_invalid_signature()
    {
        $this->seed([UserSeeder::class]);

        $user = User::first();
        $validUrl  = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $parsedUrl = parse_url($validUrl);
        parse_str($parsedUrl['query'], $queryParams);

        $validUrl = str_replace($queryParams['signature'], substr($queryParams['signature'], 0, 10) . 'sakldalsdas', $validUrl);

        $res = $this->getJson($validUrl);
        $this->isErrorSafety($res, 253);
    }

    public function test_email_url_based_on_host_requestor()
    {
        Notification::fake();
        Mail::fake();

        Notification::assertNothingSent();

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
