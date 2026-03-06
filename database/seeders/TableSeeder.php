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

        // Create 30 tables
        for ($i = 1; $i <= 30; $i++) {
            // Logic: 1-20: Empty (1), 21-27: Playing (2), 28-30: Maintenance (3)
            $status = 1;
            if ($i > 20 && $i <= 27) $status = 2;
            if ($i > 27) $status = 3;

            Table::create([
                'table_name' => 'Bàn ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'status' => $status,
                'table_price_id' => $priceId,
            ]);
        }
    }
}
