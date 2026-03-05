<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $endedAt = clone $startedAt;
        $endedAt->modify('+' . $this->faker->numberBetween(30, 180) . ' minutes');

        $table = Table::inRandomOrder()->first();
        if (!$table) {
            $table = Table::factory()->create();
        }

        return [
            'table_id' => $table->id,
            'user_id' => User::inRandomOrder()->value('id'),
            'status' => $this->faker->randomElement([0, 1, 2]),
            'started_at' => $startedAt,
            'ended_at' => $this->faker->randomElement([$endedAt, null]),
            'price_per_hour' => $table->tablePrice->price_per_hour ?? 50000,
        ];
    }
}
