<?php

namespace Database\Factories;

use App\Models\InvoiceDetail;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceDetailFactory extends Factory
{
    protected $model = InvoiceDetail::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $product->sale_price; // Sử dụng giá bán làm giá cho invoice detail
        $sub_total = $price * $quantity;
        $discount = $this->faker->randomElement([0, 5000, 10000]);
        $finalLineAmount = $sub_total - $discount;

        return [
            'invoice_id' => Invoice::factory(),
            'product_name' => $product->product_name,
            'quantity' => $quantity,
            'price' => $price,
            'sub_total' => $sub_total,
            'discount' => $discount,
            'final_line_amount' => $finalLineAmount,
        ];
    }
}
