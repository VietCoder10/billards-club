<?php

namespace App\Http\Requests\Admin\Invoice;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
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
            'order_id' => [
                'required',
                Rule::exists(Order::class, 'id')->whereNull('deleted_at')
            ],
            'payment_method' => 'required|integer|min:1|max:999999',
            'details' => 'required|array|min:1',
            'table_name' => 'nullable|string|max:255',
            'table_total' => 'required|numeric|min:0|max:999999999',
            'service_total' => 'required|numeric|min:0|max:999999999',
            'final_total' => 'required|numeric|min:0|max:999999999',
            'discount' => 'nullable|numeric|min:0|max:999999999',
            'total_minutes' => 'nullable|integer|min:0|max:999999',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Đơn hàng là trường bắt buộc',
            'order_id.exists' => 'Đơn hàng không tồn tại',
            'payment_method.required' => 'Phương thức thanh toán là trường bắt buộc',
            'payment_method.integer' => 'Phương thức thanh toán không hợp lệ',
            'payment_method.min' => 'Phương thức thanh toán không hợp lệ',
            'payment_method.max' => 'Phương thức thanh toán không hợp lệ',
            'details.required' => 'Chi tiết hóa đơn là trường bắt buộc',
            'details.array' => 'Chi tiết hóa đơn không hợp lệ',
            'details.min' => 'Phải có ít nhất một mục trong hóa đơn',
            'table_name.max' => 'Tên bàn không được vượt quá 255 ký tự',
            'table_total.required' => 'Tiền bàn là trường bắt buộc',
            'table_total.numeric' => 'Tiền bàn phải là số',
            'table_total.min' => 'Tiền bàn không được dưới 0',
            'table_total.max' => 'Tiền bàn không được vượt quá 999,999,999',
            'service_total.required' => 'Tiền dịch vụ là trường bắt buộc',
            'service_total.numeric' => 'Tiền dịch vụ phải là số',
            'service_total.min' => 'Tiền dịch vụ không được dưới 0',
            'service_total.max' => 'Tiền dịch vụ không được vượt quá 999,999,999',
            'final_total.required' => 'Tổng cộng là trường bắt buộc',
            'final_total.numeric' => 'Tổng cộng phải là số',
            'final_total.min' => 'Tổng cộng không được dưới 0',
            'final_total.max' => 'Tổng cộng không được vượt quá 999,999,999',
            'discount.numeric' => 'Giảm giá phải là số',
            'discount.min' => 'Giảm giá không được dưới 0',
            'discount.max' => 'Giảm giá không được vượt quá 999,999,999',
            'total_minutes.integer' => 'Tổng số phút phải là số nguyên',
            'total_minutes.min' => 'Tổng số phút không được dưới 0',
            'total_minutes.max' => 'Tổng số phút không được vượt quá 999,999',
        ];
    }
}
