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
        Order::factory()
            ->count(100)
            ->create()
            ->each(function ($order) {
                OrderDetail::factory()
                    ->count(rand(1, 5))
                    ->create([
                        'order_id' => $order->id,
                    ]);
            });
    }
}
