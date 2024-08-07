<?php

namespace App\Moduls\Login;

use App\Exceptions\ErrorResponse;
use App\Models\User;
use Google_Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Google extends Base
{
    public function attempt(array $cridentials): array
    {
        $validator = Validator::make($cridentials, [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        if (!isset($cridentials['token']))  throw new ErrorResponse(
            code: 422,
            message: 'Token must be sended'
        );

        $client = app(Google_Client::class);

        try {
            $payload = $client->verifyIdToken($cridentials['token']);

            if ($payload === false) {
                throw new ErrorResponse(
                    message: "Fail decode google token",
                    code: 422
                );
            }

            $user = User::where('email', $payload['email'])->first();

            if (is_null($user)) {
                $user = new User([
                    'email' => $payload['email'],
                    'name' => $payload['name'],
                    'is_anonim' => false,
                ]);
                $user->save();
                $user->markEmailAsVerified();
            }

            return [Auth::login($user), $user];
        } catch (\Exception $err) {
            if ($err instanceof ErrorResponse) {
                throw $err;
            }

            throw new ErrorResponse(
                message: "Fail decode google token",
                code: 422
            );
        }
    }
}
