<?php

namespace Tests\Mock;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

trait UserMock
{

    function _adi()
    {
        $user = User::where('email', 'adi@adikasi.com')->first();
        $token = Auth::login($user);

        return [$user, $token];
    }

    function _kasi()
    {
        $user = User::where('email', 'kasi@adikasi.com')->first();
        $token = Auth::login($user);

        return [$user, $token];
    }
}
