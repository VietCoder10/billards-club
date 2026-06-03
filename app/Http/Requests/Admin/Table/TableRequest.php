<?php

namespace App\Http\Requests\Admin\Table;

use App\Models\TablePriceMaster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TableRequest extends FormRequest
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
            'table_name' => 'required|string|max:255',
            'status' => 'required|integer|min:1|max:999999',
            'table_price_id' => [
                'required',
                Rule::exists(TablePriceMaster::class, 'id')->whereNull('deleted_at')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'table_name.required' => 'Tên bàn là trường bắt buộc',
            'table_name.max' => 'Tên bàn không được vượt quá 255 ký tự',
            'status.required' => 'Trạng thái là trường bắt buộc',
            'status.integer' => 'Trạng thái không hợp lệ',
            'status.min' => 'Trạng thái không được dưới 1',
            'status.max' => 'Trạng thái không được vượt quá 999999',
            'table_price_id.required' => 'Loại giá bàn là trường bắt buộc',
            'table_price_id.exists' => 'Loại giá bàn không tồn tại',
        ];
    }
}
