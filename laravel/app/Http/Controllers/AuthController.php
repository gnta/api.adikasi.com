<?php

namespace App\Http\Controllers;

use App\Exceptions\ErrorResponse;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\LoginService;
use App\Traits\Response;
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
            'is_anonim' => false
        ]);

        URL::forceRootUrl($req->schemeAndHttpHost());

        $token = Auth::login($user);
        event(new Registered($user));

        return $this->response(
            data: [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token
            ]
        );
    }

    public function login(Request $req)
    {
        return $this->_login($req->all());
    }

    public function loginProvider(Request $req, $provider)
    {
        return $this->_login($req->all(), $provider);
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
            message: __('messages.welcome')
        );

        return $this->response(
            data: true,
            message: $status
        );
    }

    private function _login($data, $provider = 'adikasi')
    {
        $service =  new LoginService($provider);

        [$token, $user] = $service->attempt($data);

        return $this->response(
            data: [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token
            ]
        );
    }
}
