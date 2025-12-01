<?php

namespace App\Http\Requests\Admin\Login;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

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
        $data = $this->all();

        return $validator->after(function ($validator) use ($data) {
            preg_match('/[a-z]/', $data['password'], $outLower);
            preg_match('/[A-Z]/', $data['password'], $outUpper);
            preg_match('/[0-9]/', $data['password'], $outNumber);
            preg_match('/[@$!%*?&]/', $data['password'], $outSpecial);
            if (! $outLower || ! $outUpper || ! $outNumber || ! $outSpecial) {
                $validator->errors()->add('password', 'error');
            }
        });
    }
}
