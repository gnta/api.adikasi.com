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
        $res->assertStatus(200);
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
}
