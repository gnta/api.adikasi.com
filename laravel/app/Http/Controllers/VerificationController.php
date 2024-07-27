<?php

namespace App\Http\Controllers;

use App\Exceptions\ErrorResponse;
use App\Models\User;
use App\Traits\Response;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    use Response;


    function verify($userId, Request $req)
    {
        if (!$req->hasValidSignature()) throw new ErrorResponse(
            code: 253,
        );

        $user = User::findOrFail($userId);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $this->response(data: true, message: 'Email has been verived');
    }
}
