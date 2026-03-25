<?php

namespace App\Http\Requests\Admin\Table;

use Illuminate\Foundation\Http\FormRequest;

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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'table_price_id' => 'required',
            'status' => 'required',
        ];
    }
    public function attributes(): array
    {
        return [
            'table_price_id' => 'Giá bàn',
            'status' => 'Trạng thái',
        ];
    }
    public function messages(): array
    {
        return [
            'table_price_id.required' => 'Giá bàn không được để trống',
            'status.required' => 'Trạng thái không được để trống',
        ];
    }
}
