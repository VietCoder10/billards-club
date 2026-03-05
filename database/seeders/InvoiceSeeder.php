<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Order;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy các order đã hoàn thành để tạo hóa đơn
        $completedOrders = Order::where('status', 'completed')->get();

        foreach ($completedOrders as $order) {
            $invoice = Invoice::factory()->create([
                'order_id' => $order->id,
                'created_by' => $order->user_id,
                'updated_by' => $order->user_id,
            ]);

            InvoiceDetail::factory()
                ->count(rand(1, 5))
                ->create([
                    'invoice_id' => $invoice->id,
                ]);
        }
    }
}
