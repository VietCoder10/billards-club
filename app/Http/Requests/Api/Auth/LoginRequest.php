<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\Api\ApiFormRequest;

class LoginRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required',
            'password' => 'required|min:10|max:16',
        ];
    }

    public function withValidator($validator)
    {
        $password = $this->input('password');
        return $validator->after(function ($validator) use ($password) {
            preg_match('/[a-z]/', $password, $outLower);
            preg_match('/[A-Z]/', $password, $outUpper);
            preg_match('/[0-9]/', $password, $outNumber);
            preg_match('/[@$!%*?&]/', $password, $outSpecial);
            if (!$outLower || !$outUpper || !$outNumber || !$outSpecial) {
                $validator->errors()->add('password', "error");
            }
        });
    }
}
