<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers')->whereNull('deleted_at')
            ],
            'password' => 'required|string|min:8|max:100|confirmed',
        ];
    }
    public function withValidator($validator)
    {
        $data = $this->all();
        return $validator->after(function ($validator) use ($data) {
            if (isset($data['password']) && $data['password']) {
                preg_match('/[a-z]/', $data['password'], $outLower);
                preg_match('/[A-Z]/', $data['password'], $outUpper);
                preg_match('/[0-9]/', $data['password'], $outNumber);
                if (!$outLower || !$outUpper || !$outNumber) {
                    $validator->errors()->add('password', "Mật khẩu phải có ít nhất 1 chữ cái thường, 1 chữ cái hoa và 1 số");
                }
            }
        });
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Tên là trường bắt buộc',
            'name.max' => 'Tên không được vượt quá 255 ký tự',
            'email.required' => 'Email là trường bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không được vượt quá 255 ký tự',
            'email.unique' => 'Email đã tồn tại',
            'password.required' => 'Password là trường bắt buộc',
            'password.min' => 'Password không được dưới 10 ký tự',
            'password.max' => 'Password không được vượt quá 16 ký tự',
            'password.confirmed' => 'Password và password_confirmation không khớp',
        ];
    }
}
