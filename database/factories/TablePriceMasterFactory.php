<?php

namespace Database\Factories;

use App\Models\TablePriceMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

class TablePriceMasterFactory extends Factory
{
    protected $model = TablePriceMaster::class;

    public function definition(): array
    {
        return [
            'price_name' => $this->faker->randomElement(['Bàn Thường', 'Bàn VIP', 'Bàn Thi Đấu']),
            'price_per_hour' => $this->faker->randomElement([50000, 80000, 100000]),
        ];
    }
}
