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
        $totalAmount = $this->faker->randomFloat(2, 50000, 500000);
        $discount = $this->faker->randomElement([0, 10000, 20000, 50000]);
        $tax = $totalAmount * 0.1;
        $finalAmount = $totalAmount - $discount + $tax;

        return [
            'order_id' => Order::factory(),
            'total_amount' => $totalAmount,
            'discount' => $discount,
            'tax' => $tax,
            'final_amount' => $finalAmount,
            'payment_method' => $this->faker->randomElement([1, 2]),
            'paid_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
