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
        $unitPrice = $product->sale_price;
        $subTotal = $unitPrice * $quantity;
        $discountAmount = $this->faker->randomElement([0, 5000, 10000]);
        $totalAmount = $subTotal - $discountAmount;

        return [
            'invoice_id' => Invoice::factory(),
            'item_name' => $product->product_name,
            'quantity' => $quantity,
            'price' => $unitPrice,
            'sub_total' => $subTotal,
            'discount' => $discountAmount,
            'total_amount' => $totalAmount,
        ];
    }
}
