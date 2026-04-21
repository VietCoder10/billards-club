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
                $validator->errors()->add('password', 'password phải có ít nhất 1 chữ cái thường và 1 chữ cái hoa');
            }
            preg_match('/[0-9]/', $data['password'], $outNumber);
            if (! $outNumber) {
                $validator->errors()->add('password', 'password phải có ít nhất 1 số');
            }
        });
    }
    public function messages(): array
    {
        return [
            'password.required' => 'password là trường bắt buộc',
            'password.min' => 'password không được dưới 8 ký tự',
            'password.max' => 'password không được vượt quá 100 ký tự',
            'password_confirmation.required' => 'password_confirmation là trường bắt buộc',
            'password_confirmation.same' => 'password_confirmation và password không khớp',
        ];
    }
}
