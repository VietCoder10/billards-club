<?php

namespace App\Http\Requests\Admin\Order;

use App\Models\Table;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
        $id = $this->route('order');
        $rules = [
            'table_id' => [
                'required',
                Rule::exists(Table::class, 'id')->whereNull('deleted_at')
            ],
        ];

        if ($id) {
            $rules = array_merge($rules, [
                'order_number' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('orders')->whereNull('deleted_at')->where(function ($q) use ($id) {
                        if ($id) {
                            $q->where('id', '<>', $id);
                        }
                    })
                ],
                'note' => 'nullable|string|max:255',
                'status' => 'required|integer|min:1|max:999999',
                'price_per_hour' => 'required|numeric|min:1|max:999999',
                'total_minutes' => 'nullable|integer|min:0|max:999999',
                'table_total' => 'nullable|numeric|min:0|max:999999999',
                'service_total' => 'nullable|numeric|min:0|max:999999999',
                'final_total' => 'nullable|numeric|min:0|max:999999999',
                'order_details' => 'nullable|array',
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'table_id.required' => 'Bàn là trường bắt buộc',
            'table_id.exists' => 'Bàn không tồn tại',
            'order_number.required' => 'Mã đơn hàng là trường bắt buộc',
            'order_number.max' => 'Mã đơn hàng không được vượt quá 255 ký tự',
            'order_number.unique' => 'Mã đơn hàng đã tồn tại',
            'note.max' => 'Ghi chú không được vượt quá 255 ký tự',
            'status.required' => 'Trạng thái là trường bắt buộc',
            'status.integer' => 'Trạng thái phải là số nguyên',
            'status.min' => 'Trạng thái không hợp lệ',
            'status.max' => 'Trạng thái không được vượt quá 999,999',
            'price_per_hour.required' => 'Giá mỗi giờ là trường bắt buộc',
            'price_per_hour.numeric' => 'Giá mỗi giờ phải là số',
            'price_per_hour.min' => 'Giá mỗi giờ không được dưới 1',
            'price_per_hour.max' => 'Giá mỗi giờ không được vượt quá 999,999',
            'total_minutes.integer' => 'Tổng số phút phải là số nguyên',
            'total_minutes.min' => 'Tổng số phút không được dưới 0',
            'total_minutes.max' => 'Tổng số phút không được vượt quá 999,999',
            'table_total.numeric' => 'Tổng tiền bàn phải là số',
            'table_total.min' => 'Tổng tiền bàn không được dưới 0',
            'table_total.max' => 'Tổng tiền bàn không được vượt quá 999,999,999',
            'service_total.numeric' => 'Tổng tiền dịch vụ phải là số',
            'service_total.min' => 'Tổng tiền dịch vụ không được dưới 0',
            'service_total.max' => 'Tổng tiền dịch vụ không được vượt quá 999,999,999',
            'final_total.numeric' => 'Tổng cộng phải là số',
            'final_total.min' => 'Tổng cộng không được dưới 0',
            'final_total.max' => 'Tổng cộng không được vượt quá 999,999,999',
            'order_details.array' => 'Chi tiết đơn hàng không hợp lệ',
        ];
    }
}
