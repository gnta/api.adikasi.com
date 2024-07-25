<?php

namespace App\Http\Requests;

use App\Exceptions\ErrorResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    abstract function authorize(): bool;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    abstract function rules(): array;

    protected function failedValidation(Validator $validator)
    {
        $messageBag = $validator->getMessageBag();

        throw new ErrorResponse(
            data: $messageBag,
            type: 'validation',
            message: $messageBag->first(),
            code: 422
        );
    }
}
