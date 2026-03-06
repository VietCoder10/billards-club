<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = \App\Models\Product::all();
        $users = \App\Models\User::all();

        // 1. Create PENDING orders for tables with status 2 (Playing)
        $playingTables = Table::where('status', 2)->get();
        foreach ($playingTables as $table) {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'table_id' => $table->id,
                'user_id' => $users->random()->id,
                'status' => \App\Enums\OrderStatus::PENDING,
                'started_at' => now()->subMinutes(rand(10, 120)),
                'price_per_hour' => $table->tablePrice->price_per_hour ?? 60000,
                'total_minutes' => 0, // Will be calculated in UI/Backend
                'table_total' => 0,
                'service_total' => 0,
                'final_total' => 0,
            ]);

            $this->addRandomDetails($order, $products);
        }

        // 2. Create historical orders (Completed/Cancelled)
        for ($i = 0; $i < 50; $i++) {
            $startedAt = now()->subDays(rand(1, 30))->subHours(rand(1, 10));
            $endedAt = (clone $startedAt)->addMinutes(rand(30, 240));
            $status = rand(2, 3); // Completed or Cancelled
            $table = Table::all()->random();
            $pricePerHour = $table->tablePrice->price_per_hour ?? 60000;
            $diff = $startedAt->diff($endedAt);
            $totalMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
            $tableTotal = ($totalMinutes / 60) * $pricePerHour;

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'table_id' => $table->id,
                'user_id' => $users->random()->id,
                'status' => $status,
                'started_at' => $startedAt,
                'ended_at' => $status == 2 ? $endedAt : null,
                'price_per_hour' => $pricePerHour,
                'total_minutes' => $totalMinutes,
                'table_total' => $tableTotal,
                'service_total' => 0,
                'final_total' => $tableTotal,
            ]);

            $this->addRandomDetails($order, $products);
        }
    }

    private function addRandomDetails($order, $products)
    {
        $count = rand(1, 5);
        $totalService = 0;
        for ($i = 0; $i < $count; $i++) {
            $product = $products->random();
            $qty = rand(1, 3);
            $subTotal = $qty * $product->sale_price;
            
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'quantity' => $qty,
                'price' => $product->sale_price,
                'sub_total' => $subTotal,
            ]);
            $totalService += $subTotal;
        }

        $order->service_total = $totalService;
        $order->final_total = $order->table_total + $totalService;
        $order->save();
    }
}
