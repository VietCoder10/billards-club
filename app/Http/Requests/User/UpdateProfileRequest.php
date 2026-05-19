<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $id = Auth::guard('customer')->id();
        return [
            'name' => 'required|max:255',
            'email' => [
                'required',
                'max:255',
                'email',
                Rule::unique('customers')->whereNull('deleted_at')->ignore($id)
            ],
            'phone' => 'nullable|max:20',
            'password' => 'nullable|min:10|max:16|confirmed',
            'avatar' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof UploadedFile) {
                        $validator = Validator::make([$attribute => $value], [$attribute => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240']);
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Add additional constraints on the validation process.
     */
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

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Họ tên là trường bắt buộc',
            'name.max' => 'Họ tên không được vượt quá 255 ký tự',
            'email.required' => 'Email là trường bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không được vượt quá 255 ký tự',
            'email.unique' => 'Email đã tồn tại trong hệ thống',
            'password.min' => 'Mật khẩu không được dưới 10 ký tự',
            'password.max' => 'Mật khẩu không được vượt quá 16 ký tự',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'avatar.image' => 'Ảnh đại diện phải là ảnh',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg, gif, svg',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 10MB',
        ];
    }
}
