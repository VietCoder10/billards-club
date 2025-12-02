<?php

namespace App\Http\Requests\Admin\Login;

use Illuminate\Foundation\Http\FormRequest;

class AdminResetPasswordRequest extends FormRequest
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
            'password' => 'required|min:8|max:100',
            'password_confirmation' => 'required|same:password',
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
            'password.required' => '値を入力してください',
            'password.min' => '8文字以上で入力してください',
            'password.max' => '100文字以下で入力してください',
            'password_confirmation.required' => '値を入力してください',
            'password_confirmation.same' => 'パスワードと確認用パスワードが一致しません',
        ];
    }
}
