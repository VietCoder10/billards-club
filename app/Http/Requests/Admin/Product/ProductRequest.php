<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
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

            'category' => ['nullable', 'max:255'],

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
