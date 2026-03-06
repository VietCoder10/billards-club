<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $order = Order::inRandomOrder()->first() ?? Order::factory()->create();
        $subTotal = $this->faker->randomFloat(2, 50000, 500000);
        $discountAmount = $this->faker->randomElement([0, 10000, 20000, 50000]);
        $taxAmount = $subTotal * 0.1;
        $totalAmount = $subTotal - $discountAmount + $taxAmount;

        return [
            'order_id' => $order->id,
            'table_total' => $order->table_total,
            'service_total' => $order->service_total,
            'total_amount' => $subTotal,
            'discount' => $discountAmount,
            'final_amount' => $totalAmount,
            'payment_method' => $this->faker->randomElement([1, 2]),
            'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_by' => User::inRandomOrder()->value('id') ?? User::factory(),
            'updated_by' => User::inRandomOrder()->value('id') ?? User::factory(),
        ];
    }
}
