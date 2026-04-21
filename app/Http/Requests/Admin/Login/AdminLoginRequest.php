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
            'email.required' => 'email là trường bắt buộc',
            'email.email' => 'email không đúng định dạng',
            'email.max' => 'email không được vượt quá 255 ký tự',
            'password.required' => 'password là trường bắt buộc',
            'password.min' => 'password không được dưới 8 ký tự',
            'password.max' => 'password không được vượt quá 100 ký tự',
        ];
    }
}
