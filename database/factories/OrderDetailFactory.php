<?php

namespace Database\Factories;

use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderDetailFactory extends Factory
{
    protected $model = OrderDetail::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $quantity = $this->faker->numberBetween(1, 10);
        $price = $product->sale_price; // Sử dụng giá bán làm giá cho order detail

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'product_name' => $product->product_name,
            'quantity' => $quantity,
            'price' => $price,
            'sub_total' => $price * $quantity,
        ];
    }
}
