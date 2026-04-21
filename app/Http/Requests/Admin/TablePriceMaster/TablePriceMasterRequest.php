<?php

namespace App\Http\Requests\Admin\TablePriceMaster;

use Illuminate\Foundation\Http\FormRequest;

class TablePriceMasterRequest extends FormRequest
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
        return [
            'price_name' => 'required|string|max:255',
            'price_per_hour' => 'required|numeric|min:1|max:999999',
        ];
    }

    public function messages(): array
    {
        return [
            'price_name.required' => 'Tên loại giá là trường bắt buộc',
            'price_name.max' => 'Tên loại giá không được vượt quá 255 ký tự',
            'price_per_hour.required' => 'Giá mỗi giờ là trường bắt buộc',
            'price_per_hour.numeric' => 'Giá mỗi giờ phải là số',
            'price_per_hour.min' => 'Giá mỗi giờ không được dưới 1',
            'price_per_hour.max' => 'Giá mỗi giờ không được vượt quá 999999',
        ];
    }
}
