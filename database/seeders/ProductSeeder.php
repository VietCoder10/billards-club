<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = \App\Models\Supplier::all();
        if ($suppliers->isEmpty()) {
            $suppliers = \App\Models\Supplier::factory()->count(10)->create();
        }

        $baseProducts = [
            // Đồ uống (DRINK = 2)
            ['product_name' => 'Coca Cola', 'category' => \App\Enums\ProductCategory::DRINK, 'sku' => 'COCA', 'sale_price' => 15000, 'cost_price' => 10000],
            ['product_name' => 'Pepsi', 'category' => \App\Enums\ProductCategory::DRINK, 'sku' => 'PEPSI', 'sale_price' => 15000, 'cost_price' => 10000],
            ['product_name' => '7Up', 'category' => \App\Enums\ProductCategory::DRINK, 'sku' => '7UP', 'sale_price' => 15000, 'cost_price' => 10000],
            ['product_name' => 'Nước khoáng Lavie', 'category' => \App\Enums\ProductCategory::DRINK, 'sku' => 'LAVIE', 'sale_price' => 10000, 'cost_price' => 5000],
            ['product_name' => 'Nước tinh khiết Aquafina', 'category' => \App\Enums\ProductCategory::DRINK, 'sku' => 'AQUA', 'sale_price' => 10000, 'cost_price' => 5000],
            ['product_name' => 'Bò húc (Redbull)', 'category' => \App\Enums\ProductCategory::DRINK, 'sku' => 'REDBULL', 'sale_price' => 20000, 'cost_price' => 15000],
            ['product_name' => 'Sting Dâu', 'category' => \App\Enums\ProductCategory::DRINK, 'sku' => 'STING', 'sale_price' => 15000, 'cost_price' => 10000],
            ['product_name' => 'Trà xanh C2', 'category' => \App\Enums\ProductCategory::DRINK, 'sku' => 'C2', 'sale_price' => 12000, 'cost_price' => 8000],
            
            // Đồ ăn (FOOD = 1)
            ['product_name' => 'Bánh mì thịt', 'category' => \App\Enums\ProductCategory::FOOD, 'sku' => 'BANHMI', 'sale_price' => 25000, 'cost_price' => 15000],
            ['product_name' => 'Phở bò', 'category' => \App\Enums\ProductCategory::FOOD, 'sku' => 'PHO', 'sale_price' => 45000, 'cost_price' => 30000],
            ['product_name' => 'Cơm tấm sườn bì chả', 'category' => \App\Enums\ProductCategory::FOOD, 'sku' => 'COMTAM', 'sale_price' => 40000, 'cost_price' => 25000],
            ['product_name' => 'Mì tôm trứng', 'category' => \App\Enums\ProductCategory::FOOD, 'sku' => 'MITOM', 'sale_price' => 20000, 'cost_price' => 12000],
            ['product_name' => 'Xúc xích Đức nướng', 'category' => \App\Enums\ProductCategory::FOOD, 'sku' => 'XUCXICH', 'sale_price' => 15000, 'cost_price' => 10000],
            
            // Thuê Gậy (CUE_RENTAL = 3)
            ['product_name' => 'Gậy Adam II', 'category' => \App\Enums\ProductCategory::CUE_RENTAL, 'sku' => 'GAY-ADAM', 'sale_price' => 50000, 'cost_price' => 0],
            ['product_name' => 'Gậy Carbon', 'category' => \App\Enums\ProductCategory::CUE_RENTAL, 'sku' => 'GAY-CARBON', 'sale_price' => 100000, 'cost_price' => 0],
            ['product_name' => 'Gậy Fury', 'category' => \App\Enums\ProductCategory::CUE_RENTAL, 'sku' => 'GAY-FURY', 'sale_price' => 70000, 'cost_price' => 0],
        ];

        for ($i = 1; $i <= 100; $i++) {
            $base = $baseProducts[array_rand($baseProducts)];
            Product::create([
                'product_name' => $base['product_name'] . ' #' . $i,
                'category' => $base['category'],
                'sku' => $base['sku'] . '-' . str_pad((string)$i, 3, '0', STR_PAD_LEFT),
                'sale_price' => $base['sale_price'],
                'cost_price' => $base['cost_price'],
                'supplier_id' => $suppliers->random()->id,
                'quantity' => rand(20, 100),
                'description' => 'Sản phẩm phục vụ tại câu lạc bộ.',
            ]);
        }
    }
}
