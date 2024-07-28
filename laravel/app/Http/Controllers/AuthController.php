<?php

namespace App\Http\Controllers;

use App\Exceptions\ErrorResponse;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Traits\Response;
use Error;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    use Response;

    public function register(RegisterRequest $req)
    {
        $data = $req->validated();
        $user = new User();

        $user =  User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        URL::forceRootUrl($req->schemeAndHttpHost());

        $token = Auth::login($user);
        event(new Registered($user));
        $user->sendEmailVerificationNotification();

        return $this->response(
            data: [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token
            ]
        );
    }

    public function login(LoginRequest $req)
    {
        $data = $req->validated();

        if (!$token = Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password']
        ])) throw new ErrorResponse(
            code: 422,
            message: 'Invalid email or password'
        );

        $user = Auth::user();

        return $this->response(
            data: [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token
            ]
        );
    }

    public function forgotPassword(ForgotPasswordRequest $req)
    {
        $data = $req->validated();

        $user = User::where('email', $data['email'])
            ->select('email')
            ->first();

        if (is_null($user)) throw new ErrorResponse(
            code: 404,
            message: 'Email not found'
        );

        URL::forceRootUrl($req->schemeAndHttpHost());

        $status = Password::sendResetLink([
            'email' => $user->email
        ]);

        if ($status !== Password::RESET_LINK_SENT) throw new ErrorResponse(
            code: 422,
            message: $status
        );

        return $this->response(
            data: true,
            message: $status
        );
    }

    public function resetPassword(ResetPasswordRequest $req)
    {
        $data = $req->validated();

        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'token' => $data['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password
                ]);

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) throw new ErrorResponse(
            code: 422,
            message: $status
        );

        return $this->response(
            data: true,
            message: $status
        );
    }
}
