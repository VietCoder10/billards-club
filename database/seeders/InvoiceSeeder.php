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
        // Lấy các order đã hoàn thành (status = 2: Hoàn thành) để tạo hóa đơn
        $completedOrders = Order::where('status', \App\Enums\OrderStatus::COMPLETED)->get();

        foreach ($completedOrders as $order) {
            $invoice = Invoice::create([
                'invoice_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'table_name' => $order->table->table_name ?? 'Bàn ' . rand(1, 10),
                'table_total' => $order->table_total,
                'service_total' => $order->service_total,
                'total_amount' => $order->final_total,
                'discount' => 0,
                'final_amount' => $order->final_total,
                'payment_method' => rand(1, 2),
                'created_by' => $order->user_id,
                'updated_by' => $order->user_id,
            ]);

            foreach ($order->details as $detail) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'item_name' => $detail->product_name,
                    'quantity' => $detail->quantity,
                    'price' => $detail->price,
                    'sub_total' => $detail->sub_total,
                ]);
            }
        }
    }
}
