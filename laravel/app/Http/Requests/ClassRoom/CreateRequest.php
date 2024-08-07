<?php

namespace App\Http\Requests\ClassRoom;

use Illuminate\Support\Facades\Auth;

class CreateRequest extends \App\Http\Requests\Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'students' => 'nullable|array',
            'students.*.name' => 'string|required_with:students',
            'students.*.email' => 'nullable|email',
        ];
    }
}
