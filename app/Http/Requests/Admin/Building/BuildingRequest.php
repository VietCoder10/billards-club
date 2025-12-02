<?php

namespace App\Http\Requests\Admin\Building;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Building;
use Illuminate\Validation\Rule;

class BuildingRequest extends FormRequest
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
        $id = $this->building;
        return [
            'building_name' => [
                'required',
                'max:255',
            ],
            'building_name_kana' => [
                'required',
                'max:255',
                'regex:/^([ァ-ンｧ-ﾝﾞﾟ]|ー|　| |（|）|\(|\))+$/u',
            ],
            'building_code' => [
                'nullable',
                'max:255',
                'regex:/^[A-Za-z0-9]*$/',
                Rule::unique(Building::class, 'building_code')
                    ->where(function ($q) use ($id) {
                        if ($id) {
                            $q->where('id', '<>', $id);
                        }
                    })
                    ->whereNull('deleted_at'),
            ],
            'person_in_charge' => [
                'nullable',
                'max:255',
            ],
            'construction_reason' => [
                'nullable',
                'max:255',
            ],
            'building_short_name' => [
                'nullable',
                'max:255',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'building_name.required' => '値を入力してください',
            'building_name.max' => '255文字以下で入力してください',
            'building_name_kana.required' => '値を入力してください',
            'building_name_kana.max' => '255文字以下で入力してください',
            'building_name_kana.regex' => 'ふりがなは全角カタカナで入力してください',
            'building_code.max' => '255文字以下で入力してください',
            'building_code.regex' => '建物コードは半角英数字で入力してください',
            'building_code.unique' => '既に登録されている建物コードです',
            'person_in_charge.max' => '255文字以下で入力してください',
            'construction_reason.max' => '255文字以下で入力してください',
            'building_short_name.max' => '255文字以下で入力してください',
        ];
    }

}
