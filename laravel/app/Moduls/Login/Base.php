<?php

namespace App\Moduls\Login;

use App\Contracts\Authenticator as Contract;
use App\Exceptions\ErrorResponse;

abstract class Base implements Contract
{
    public function failedValidation(\Illuminate\Validation\Validator $validator)
    {
        $errors = $validator->errors();

        throw new ErrorResponse(
            code: 422,
            message: $errors->first(),
            data: [
                'form' => $errors
            ]
        );
    }
}
