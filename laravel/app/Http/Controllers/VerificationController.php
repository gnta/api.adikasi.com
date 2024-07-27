<?php

namespace App\Http\Controllers;

use App\Exceptions\ErrorResponse;
use App\Models\User;
use App\Traits\Response;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    use Response;

    function verify($userId, $hash, Request $req)
    {

        if (!$req->hasValidSignature()) throw new ErrorResponse(
            code: 253,
        );

        $user = User::find($userId);

        if (is_null($user)) throw new ErrorResponse(
            code: 253,
        );

        if (!hash_equals((string) $user->getKey(), (string) $userId)) throw new ErrorResponse(
            code: 253,
        );

        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) throw new ErrorResponse(
            code: 253,
        );

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }


        return $this->response(
            data: true,
            message: 'Email has been verived'
        );
    }
}
