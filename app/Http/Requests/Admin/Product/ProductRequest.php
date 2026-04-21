<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class ProductRequest extends FormRequest
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
        $id = $this->route('product');
        return [
            'product_name' => 'required|string|max:255',
            'category' => 'required|integer|min:1|max:999999',
            'sku' => 'required|string|max:255|unique:products,sku' . ($id ? ',' . $id : ''),
            'supplier_id' => 'required|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:1|max:999999',
            'sale_price' => 'required|numeric|min:1|max:999999',
            'quantity' => 'required|integer|min:1|max:999999',
            'description' => 'nullable|string|max:255',
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
    }

    public function messages(): array
    {
        return [
            'product_name.required' => 'Tên sản phẩm là trường bắt buộc',
            'product_name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự',
            'category.required' => 'Danh mục là trường bắt buộc',
            'category.integer' => 'Danh mục phải là số nguyên',
            'category.min' => 'Danh mục không được dưới 1',
            'category.max' => 'Danh mục không được vượt quá 999999',
            'sku.required' => 'Mã sản phẩm là trường bắt buộc',
            'sku.max' => 'Mã sản phẩm không được vượt quá 255 ký tự',
            'sku.unique' => 'Mã sản phẩm đã tồn tại',
            'supplier_id.required' => 'Nhà cung cấp là trường bắt buộc',
            'supplier_id.exists' => 'Nhà cung cấp không tồn tại',
            'cost_price.required' => 'Giá nhập là trường bắt buộc',
            'cost_price.numeric' => 'Giá nhập phải là số',
            'cost_price.min' => 'Giá nhập không được dưới 1',
            'cost_price.max' => 'Giá nhập không được vượt quá 999999',
            'sale_price.required' => 'Giá bán là trường bắt buộc',
            'sale_price.numeric' => 'Giá bán phải là số',
            'sale_price.min' => 'Giá bán không được dưới 1',
            'sale_price.max' => 'Giá bán không được vượt quá 999999',
            'quantity.required' => 'Số lượng là trường bắt buộc',
            'quantity.integer' => 'Số lượng phải là số nguyên',
            'quantity.min' => 'Số lượng không được dưới 1',
            'quantity.max' => 'Số lượng không được vượt quá 999999',
            'description.max' => 'Mô tả không được vượt quá 255 ký tự',
            'avatar.image' => 'Ảnh đại diện phải là ảnh',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg, gif, svg',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 10MB',
        ];
    }
}
