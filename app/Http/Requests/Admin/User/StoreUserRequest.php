<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        $id = $this->user;
        $validate = [
            'name' => 'required|max:255',
            'email' => [
                'required',
                'max:255',
                Rule::unique('users')->whereNull('deleted_at')->where(function ($q) use ($id) {
                    if ($id) {
                        $q->where('id', '<>', $id);
                    }
                })
            ],
            'password' => 'required|min:10|max:16',
        ];
        if ($id) {
            $validate['password'] = 'min:10|max:16';
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
                preg_match('/[@$!%*?&]/', $data['password'], $outSpecial);
                if (!$outLower || !$outUpper || !$outNumber || !$outSpecial) {
                    $validator->errors()->add('password', "error");
                }
            }
        });
    }
}
