<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;


class ForgotPasswordTest extends TestCase
{
    protected $payload = [];
    function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from password_reset_tokens');

        $this->seed([UserSeeder::class]);

        $user = User::first();

        $this->payload = [
            'email' => $user->email
        ];
    }


    public function test_success(): void
    {
        Notification::fake();
        Mail::fake();

        $res = $this->postJson('/forgot-password', $this->payload);
        $res->assertStatus(200);
    }

    public function test_success_resend_twice_but_not_sending_a_new_notification(): void
    {
        Notification::fake();
        Mail::fake();
        $res = $this->postJson('/forgot-password', $this->payload);
        $res->assertStatus(200);
        $res = $this->postJson('/forgot-password', $this->payload);
        $res->assertStatus(422);
        Notification::assertCount(1);
    }

    public function test_fail_not_sending_email(): void
    {
        $res = $this->postJson('/forgot-password', []);
        $this->isErrorSafety($res, 422);
    }

    public function test_fail_not_found_email(): void
    {
        $res = $this->postJson('/forgot-password', [
            'email' => 'notfound@test.com'
        ]);
        $this->isErrorSafety($res, 404);
    }

    public function test_email_url_based_on_host_requestor()
    {
        Notification::fake();
        Mail::fake();

        Notification::assertNothingSent();

        $faker = \Faker\Factory::create();
        $domain = $faker->domainName();

        $request = Request::create('/forgot-password', 'POST', [], [], [], [
            'HTTP_HOST' => $domain,
            'HTTPS' => 'on',
            'SERVER_NAME' => $domain,
            'SERVER_PORT' => '443',
            'REQUEST_URI' => '/forgot-password',
        ], json_encode($this->payload));


        $request->headers->set('Content-Type', 'application/json');

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $user = User::first();

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, $domain) {
            $mail = $notification->toMail($user);

            $isRightLinkUrl = str_contains(
                $mail->actionUrl,
                "https://$domain/reset-password"
            );

            return $isRightLinkUrl;
        });
    }

    public function test_reset_password_success()
    {
        [$user, $token, $queryParams] = $this->_mailing();

        $res = $this->postJson('/reset-password', [
            'token' => $token,
            'email' => $queryParams['email'],
            'password' => 'baru',
            'password_confirmation' => 'baru'
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'data' => true
        ]);

        $newUser = User::first();

        $this->assertNotEquals($newUser->password, $user->password);
        $this->assertNotEquals($newUser->password, 'baru');
    }

    public function test_reset_password_fail_password_not_match()
    {
        [$user, $token, $queryParams] = $this->_mailing();

        $res = $this->postJson('/reset-password', [
            'token' => $token,
            'email' => $queryParams['email'],
            'password' => 'baru',
            'password_confirmation' => 'aaa'
        ]);
        $this->isErrorSafety($res, 422);
    }

    public function test_reset_password_fail_invalid_email()
    {
        [$user, $token, $queryParams] = $this->_mailing();

        $res = $this->postJson('/reset-password', [
            'token' => $token,
            'email' => 'none@test.com',
            'password' => 'baru',
            'password_confirmation' => 'baru'
        ]);
        $this->isErrorSafety($res, 422);
    }

    public function test_reset_password_fail_invalid_token()
    {
        [$user, $token, $queryParams] = $this->_mailing();

        $res = $this->postJson('/reset-password', [
            'token' => $token . 'adsasda',
            'email' => $queryParams['email'],
            'password' => 'baru',
            'password_confirmation' => 'baru'
        ]);

        $this->isErrorSafety($res, 422);
    }

    public function test_reset_password_fail_invalid_not_sending_data_at_all()
    {
        [$user, $token, $queryParams] = $this->_mailing();

        $res = $this->postJson('/reset-password', []);

        $this->isErrorSafety($res, 422);
    }
    public function test_reset_password_fail_cause_user_no_sending_request()
    {
        $user = User::first();

        $res = $this->postJson('/reset-password', [
            'token' => '2043ed8dc1df13d3a48a264fa143f14838286f824442d89590edde917874d93f',
            'email' => $user->email,
            'password' => 'baru',
            'password_confirmation' => 'baru'
        ]);

        $this->isErrorSafety($res, 422);
    }

    private function _mailing()
    {
        Notification::fake();
        Mail::fake();

        $user = User::first();
        $this->assertNotNull($user, 'User not found');
        $this->assertNotEmpty($user->email, 'User does not have an email');

        $status = Password::sendResetLink([
            'email' => $user->email
        ]);

        Log::info($status);

        $actionUrl = '';
        $token = '';

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, &$actionUrl, &$token) {
            $mail = $notification->toMail($user);
            $actionUrl = $mail->actionUrl;
            $token = $notification->token;

            return true;
        });

        $this->assertNotEmpty($actionUrl);
        $this->assertNotEmpty($token);

        $parsedUrl = parse_url($actionUrl);
        parse_str($parsedUrl['query'], $queryParams);


        return [$user, $token, $queryParams];
    }
}
