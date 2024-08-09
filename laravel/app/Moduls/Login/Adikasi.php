<?php

namespace App\Moduls\Login;

use App\Exceptions\ErrorResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Adikasi extends Base
{
    public function attempt(array $cridentials): array
    {

        $validator = Validator::make($cridentials, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        if (!isset($cridentials['email']) || !isset($cridentials['password']))  throw new ErrorResponse(
            code: 422,
            message: 'Email or password must be sended'
        );

        if (!$token = Auth::attempt([
            'email' => $cridentials['email'],
            'password' => $cridentials['password']
        ])) throw new ErrorResponse(
            code: 422,
            message: 'Invalid email or password'
        );

        $user = Auth::user();
        return [$token, $user];
    }
}
