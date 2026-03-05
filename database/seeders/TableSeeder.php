<?php

namespace Database\Seeders;

use App\Models\TablePriceMaster;
use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $priceId = TablePriceMaster::where('price_name', 'Bàn Thường')->value('id') ?? TablePriceMaster::first()->id;

        for (
            $i = 1;
            $i <= 100;
            $i++
        ) {
            Table::create([
                'table_name' => 'Bàn ' . $i,
                'status' => 0,
                'table_price_id' => $priceId,
            ]);
        }
    }
}
