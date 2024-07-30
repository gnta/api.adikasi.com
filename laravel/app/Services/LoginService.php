<?php

namespace App\Services;

use App\Contracts\Authenticator as Contract;
use App\Exceptions\ErrorResponse;

class LoginService
{

    private Contract $provider;

    public function __construct(string $provider)
    {
        $this->setProvider($provider);
    }

    public function attempt(array $cridentials)
    {
        return $this->provider->attempt($cridentials);
    }

    public function setProvider(string $provider)
    {
        $class = config("authenticator.providers.$provider");

        if (is_null($class)) {
            throw new ErrorResponse(
                message: "Not supported authenticator for {$provider}",
                code: 500
            );
        }

        $this->provider = new $class;
    }
}
