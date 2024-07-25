<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\Response;
use Illuminate\Http\Request;

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
            data: $user
        );
    }
}
