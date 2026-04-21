<?php

namespace App\Http\Requests\Admin\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
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
            'supplier_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'note' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_name.required' => 'Tên nhà cung cấp là trường bắt buộc',
            'supplier_name.max' => 'Tên nhà cung cấp không được vượt quá 255 ký tự',
            'contact_person.max' => 'Người liên hệ không được vượt quá 255 ký tự',
            'email.required' => 'Email là trường bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại là trường bắt buộc',
            'phone.max' => 'Số điện thoại không được vượt quá 255 ký tự',
            'address.required' => 'Địa chỉ là trường bắt buộc',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự',
            'note.max' => 'Ghi chú không được vượt quá 255 ký tự',
            'status.required' => 'Trạng thái là trường bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ];
    }
}
