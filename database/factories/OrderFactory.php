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

        $pricePerHour = $table->tablePrice->price_per_hour ?? 50000;
        $diff = $startedAt->diff($endedAt);
        $totalMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
        $tableTotal = ($totalMinutes / 60) * $pricePerHour;

        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numerify('##########'),
            'table_id' => $table->id,
            'user_id' => User::inRandomOrder()->value('id'),
            'note' => $this->faker->sentence,
            'status' => $this->faker->randomElement([1, 2, 3]),
            'started_at' => $startedAt,
            'ended_at' => $this->faker->randomElement([$endedAt, null]),
            'price_per_hour' => $pricePerHour,
            'total_minutes' => $totalMinutes,
            'table_total' => $tableTotal,
            'service_total' => 0,
            'final_total' => $tableTotal,
        ];
    }
}
