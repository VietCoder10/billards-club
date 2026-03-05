<?php

namespace Database\Seeders;

use App\Models\TablePriceMaster;
use Illuminate\Database\Seeder;

class TablePriceMasterSeeder extends Seeder
{
    public function run(): void
    {
        $prices = [
            ['price_name' => 'Bàn Thường', 'price_per_hour' => 50000],
            ['price_name' => 'Bàn VIP', 'price_per_hour' => 80000],
            ['price_name' => 'Bàn Thi Đấu', 'price_per_hour' => 100000],
        ];

        foreach ($prices as $price) {
            TablePriceMaster::updateOrCreate(
                ['price_name' => $price['price_name']],
                ['price_per_hour' => $price['price_per_hour']]
            );
        }
    }
}
