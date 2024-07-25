<?php

namespace App\Http\Controllers;

use App\Exceptions\ErrorResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use Response;

    public function register(RegisterRequest $req)
    {
        $data = $req->validated();
        $user = new User();

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];

        $user->save();


        return $this->response(
            data: [
                'name' => $user->name,
                'email' => $user->email,
                'token' => Auth::login($user)
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
}
