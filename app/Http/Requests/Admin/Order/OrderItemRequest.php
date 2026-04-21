<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class OrderItemRequest extends FormRequest
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
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:999999',
            'price' => 'required|numeric|min:1|max:999999',
            'sub_total' => 'required|numeric|min:1|max:999999',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Đơn hàng là trường bắt buộc',
            'order_id.exists' => 'Đơn hàng không tồn tại',
            'product_id.required' => 'Sản phẩm là trường bắt buộc',
            'product_id.exists' => 'Sản phẩm không tồn tại',
            'product_name.required' => 'Tên sản phẩm là trường bắt buộc',
            'product_name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự',
            'quantity.required' => 'Số lượng là trường bắt buộc',
            'quantity.integer' => 'Số lượng phải là số nguyên',
            'quantity.min' => 'Số lượng không được dưới 1',
            'quantity.max' => 'Số lượng không được vượt quá 999999',
            'price.required' => 'Giá là trường bắt buộc',
            'price.numeric' => 'Giá phải là số',
            'price.min' => 'Giá không được dưới 1',
            'price.max' => 'Giá không được vượt quá 999999',
            'sub_total.required' => 'Thành tiền là trường bắt buộc',
            'sub_total.numeric' => 'Thành tiền phải là số',
            'sub_total.min' => 'Thành tiền không được dưới 1',
            'sub_total.max' => 'Thành tiền không được vượt quá 999999',
        ];
    }
}
