<?php

namespace App\Http\Requests\Admin\Login;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class AdminForgotPasswordRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::exists(User::class, 'email')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => '値を入力してください',
            'email.email' => 'メールアドレスの形式（xxx@yyyy.zzz）で入力してください',
            'email.max' => '255文字以下で入力してください',
            'email.exists' => '登録されていないメールアドレスです',
        ];
    }
}
