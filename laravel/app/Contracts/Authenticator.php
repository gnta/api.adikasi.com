<?php

namespace App\Contracts;

interface Authenticator
{
    /**
     * This function returns an indexed array containing a token and a user.
     *
     * @return array{
     *  0: string,
     *  1: \App\Models\User
     * }
     */
    public function attempt(array $cridentials): array;
}
