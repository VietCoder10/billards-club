<?php

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_name' => $this->faker->word,
            'category' => $this->faker->randomElement(ProductCategory::getValues()),
            'sku' => $this->faker->unique()->bothify('PROD-####'),
            'supplier_id' => Supplier::factory(),
            'cost_price' => $this->faker->randomFloat(2, 5000, 100000),
            'sale_price' => $this->faker->randomFloat(2, 10000, 150000),
            'total_amount' => $this->faker->randomFloat(2, 10000, 150000),
            'quantity' => $this->faker->numberBetween(10, 100),
            'description' => $this->faker->paragraph,
        ];
    }
}
