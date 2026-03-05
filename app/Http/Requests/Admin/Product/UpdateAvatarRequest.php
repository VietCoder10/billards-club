<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
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
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.required' => 'Vui lòng chọn hình ảnh.',
            'avatar.image' => 'Vui lòng chọn tệp hình ảnh hợp lệ.',
            'avatar.mimes' => 'Chỉ chấp nhận các định dạng: jpeg, png, jpg, gif.',
            'avatar.max' => 'Kích thước tệp không được vượt quá 2MB.',
        ];
    }
}
