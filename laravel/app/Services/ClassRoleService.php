<?php

namespace App\Services;

use App\Exceptions\ErrorResponse;
use App\Models\ClassRole;
use Illuminate\Support\Str;

class ClassRoleService
{
    static function get(string $name): ClassRole
    {
        $slug = Str::slug($name);

        $role = ClassRole::where('slug', $slug)->first();

        if (is_null($role)) throw new ErrorResponse(
            code: 500,
            message: 'Role is not supported'
        );

        return $role;
    }
}
