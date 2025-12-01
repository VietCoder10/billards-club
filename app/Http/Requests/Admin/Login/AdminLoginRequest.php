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
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|max:100',
        ];
    }

    public function withValidator($validator)
    {
        $data = $this->all();

        return $validator->after(function ($validator) use ($data) {
            preg_match('/[a-z]/', $data['password'], $outLower);
            preg_match('/[A-Z]/', $data['password'], $outUpper);
            if (! $outLower || ! $outUpper) {
                $validator->errors()->add('password', '半角英小文字・半角英大文字をパスワードに含めてください');
            }
            preg_match('/[0-9]/', $data['password'], $outNumber);
            if (! $outNumber) {
                $validator->errors()->add('password', '半角数字をパスワードに含めてください');
            }
        });
    }
    public function messages(): array
    {
        return [
            'email.required' => '値を入力してください',
            'email.email' => 'メールアドレスの形式（xxx@yyyy.zzz）で入力してください',
            'email.max' => '255文字以下で入力してください',
            'password.required' => '値を入力してください',
            'password.min' => '8文字以上で入力してください',
            'password.max' => '100文字以下で入力してください',
        ];
    }
}
