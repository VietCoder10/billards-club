<?php

namespace App\Http\Requests\Admin\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
        $id = $this->customer;
        $validate = [
            'name' => 'required|max:255',
            'email' => [
                'required',
                'max:255',
                Rule::unique('customers')->whereNull('deleted_at')->where(function ($q) use ($id) {
                    if ($id) {
                        $q->where('id', '<>', $id);
                    }
                })
            ],
            'phone' => 'nullable|max:20',
            'password' => 'required|min:10|max:16|confirmed',
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
        if ($id) {
            $validate['password'] = 'nullable|min:10|max:16|confirmed';
        }
        return $validate;
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
            'avatar.image' => 'Ảnh đại diện phải là ảnh',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg, gif, svg',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 10240 ký tự',
        ];
    }
}
