<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product');
        // Nếu route là products/{product}

        return [
            'product_name' => ['required', 'string', 'max:255'],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048' // 2MB
            ],

            'category' => ['nullable', 'string', 'max:255'],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId)
            ],

            'supplier_id' => [
                'required',
                'exists:suppliers,id'
            ],

            // 'price' => [
            //     'required',
            //     'numeric',
            //     'min:0'
            // ],

            'quantity' => [
                'required',
                'integer',
                'min:0'
            ],

            'description' => ['nullable', 'string'],
        ];
    }
}
