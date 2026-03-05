<?php

namespace Database\Factories;

use App\Models\TablePriceMaster;
use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'table_name' => 'Bàn ' . $this->faker->unique()->numberBetween(1, 20),
            'status' => $this->faker->randomElement(TableStatus::getValues()),
            'table_price_id' => TablePriceMaster::inRandomOrder()->value('id') ?? TablePriceMaster::factory(),
        ];
    }
}
