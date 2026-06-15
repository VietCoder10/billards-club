<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReportSeeder extends Seeder
{
    /**
     * Tạo dữ liệu Orders + Invoices cho tháng 6/2026 phục vụ báo cáo doanh thu.
     *
     * Chiến lược sinh dữ liệu hợp lý:
     *  - Cuối tuần (Thứ 6, 7, CN) đông hơn ngày thường (2-3x đơn)
     *  - Giờ cao điểm: 14h-22h (ca chiều + tối)
     *  - Giờ thấp điểm: 0h-8h (ca đêm)
     *  - ~3-5 đơn/ngày thường, ~7-10 đơn/ngày cuối tuần
     *  - Mỗi đơn gồm tiền bàn + đồ uống/đồ ăn ngẫu nhiên
     *  - Tạo cả Invoice tương ứng cho mỗi đơn đã hoàn thành
     */
    public function run(): void
    {
        $tables    = Table::all();
        $products  = Product::all();
        $users     = User::all();
        $customers = Customer::all();

        if ($tables->isEmpty()) {
            $this->command->warn('Không có bàn nào. Hãy chạy TableSeeder trước.');
            return;
        }
        if ($users->isEmpty()) {
            $this->command->warn('Không có user nào. Hãy chạy UserSeeder trước.');
            return;
        }

        $year  = 2026;
        $month = 6;
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        // Khung giờ: trọng số theo giờ trong ngày (cao điểm 14h-22h)
        $hourWeights = [
            0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1,   // ca đêm: ít
            6 => 2, 7 => 2, 8 => 3, 9 => 3, 10 => 4, 11 => 4,  // ca sáng: trung bình
            12 => 5, 13 => 6, 14 => 8, 15 => 8, 16 => 8, 17 => 7, // ca chiều: cao
            18 => 9, 19 => 9, 20 => 10, 21 => 10, 22 => 8, 23 => 6, // ca tối: cao nhất
        ];

        $totalOrders   = 0;
        $totalInvoices = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date      = Carbon::create($year, $month, $day);
            $dayOfWeek = $date->dayOfWeek; // 0=CN, 6=Thứ 7

            // Số đơn trong ngày
            $isWeekend = in_array($dayOfWeek, [0, 5, 6]); // CN, T6, T7
            $orderCount = $isWeekend ? rand(7, 12) : rand(3, 6);

            for ($o = 0; $o < $orderCount; $o++) {
                // Chọn giờ bắt đầu theo trọng số
                $startHour   = $this->weightedRandom($hourWeights);
                $startMinute = rand(0, 59);

                $startedAt = $date->copy()->setTime($startHour, $startMinute, 0);

                // Thời gian chơi: 30 phút đến 3.5 giờ (phân bố thực tế)
                $totalMinutes = $this->randomPlayTime();
                $endedAt      = $startedAt->copy()->addMinutes($totalMinutes);

                // Đảm bảo không vượt qua nửa đêm ngày hôm đó+1
                if ($endedAt->gt($date->copy()->addDay()->startOfDay())) {
                    $endedAt      = $date->copy()->addDay()->setTime(0, 0, 0);
                    $totalMinutes = (int) $startedAt->diffInMinutes($endedAt);
                }

                // Chọn bàn ngẫu nhiên
                $table        = $tables->random();
                $pricePerHour = $table->tablePrice->price_per_hour ?? 60000;
                $tableTotal   = round(($totalMinutes / 60) * $pricePerHour, 2);

                $user     = $users->random();
                $customer = $customers->isNotEmpty() && rand(0, 1) ? $customers->random() : null;

                // Tạo Order COMPLETED
                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                    'table_id'     => $table->id,
                    'user_id'      => $user->id,
                    'status'       => OrderStatus::COMPLETED,
                    'started_at'   => $startedAt,
                    'ended_at'     => $endedAt,
                    'price_per_hour' => $pricePerHour,
                    'total_minutes'  => $totalMinutes,
                    'table_total'    => $tableTotal,
                    'service_total'  => 0,
                    'final_total'    => $tableTotal,
                ]);

                // Thêm order details (đồ uống / đồ ăn)
                $serviceTotal = 0;
                if ($products->isNotEmpty()) {
                    $detailCount = rand(0, 4);
                    $addedProducts = [];

                    for ($d = 0; $d < $detailCount; $d++) {
                        $product = $products->random();

                        // Tránh thêm trùng sản phẩm
                        if (in_array($product->id, $addedProducts)) {
                            continue;
                        }
                        $addedProducts[] = $product->id;

                        $qty      = rand(1, 4);
                        $price    = $product->sale_price ?? $product->price ?? 30000;
                        $subTotal = $qty * $price;

                        OrderDetail::create([
                            'order_id'     => $order->id,
                            'product_id'   => $product->id,
                            'product_name' => $product->product_name,
                            'quantity'     => $qty,
                            'price'        => $price,
                            'sub_total'    => $subTotal,
                        ]);

                        $serviceTotal += $subTotal;
                    }
                }

                // Cập nhật lại service_total & final_total
                $finalTotal            = $tableTotal + $serviceTotal;
                $order->service_total  = $serviceTotal;
                $order->final_total    = $finalTotal;
                $order->save();

                // Tạo Invoice tương ứng
                $discount = $this->calcDiscount($finalTotal, $customer);
                $finalAmount = $finalTotal - $discount;

                $invoice = Invoice::create([
                    'customer_id'    => $customer?->id,
                    'invoice_number' => 'INV-' . $date->format('Ymd') . '-' . strtoupper(Str::random(5)),
                    'table_name'     => $table->table_name,
                    'table_total'    => $tableTotal,
                    'service_total'  => $serviceTotal,
                    'total_amount'   => $finalTotal,
                    'discount'       => $discount,
                    'final_amount'   => $finalAmount,
                    'payment_method' => rand(1, 2), // 1: Tiền mặt, 2: Chuyển khoản
                    'paid_at'        => $endedAt,
                    'created_by'     => $user->id,
                    'updated_by'     => $user->id,
                    'created_at'     => $endedAt,
                    'updated_at'     => $endedAt,
                ]);

                // Tạo Invoice Details (tiền bàn + từng dịch vụ)
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'item_name'  => 'Tiền bàn ' . $table->table_name . ' (' . $totalMinutes . ' phút)',
                    'quantity'   => 1,
                    'price'      => $tableTotal,
                    'sub_total'  => $tableTotal,
                ]);

                // Lấy lại order details để tạo invoice details
                $order->details()->each(function ($detail) use ($invoice) {
                    InvoiceDetail::create([
                        'invoice_id' => $invoice->id,
                        'item_name'  => $detail->product_name,
                        'quantity'   => $detail->quantity,
                        'price'      => $detail->price,
                        'sub_total'  => $detail->sub_total,
                    ]);
                });

                $totalOrders++;
                $totalInvoices++;
            }
        }

        $this->command->info("✅ ReportSeeder hoàn thành!");
        $this->command->info("   Đã tạo {$totalOrders} đơn hàng và {$totalInvoices} hóa đơn cho tháng 6/{$year}.");
    }

    /**
     * Thời gian chơi ngẫu nhiên theo phân bố thực tế của quán billiards.
     * Đa phần 1-2 giờ, một số ít chơi dài hơn.
     */
    private function randomPlayTime(): int
    {
        $rand = rand(1, 100);
        if ($rand <= 15) {
            return rand(30, 59);   // 15%: chơi dưới 1 giờ
        } elseif ($rand <= 45) {
            return rand(60, 90);   // 30%: 1 - 1.5 giờ
        } elseif ($rand <= 75) {
            return rand(91, 120);  // 30%: 1.5 - 2 giờ
        } elseif ($rand <= 90) {
            return rand(121, 180); // 15%: 2 - 3 giờ
        } else {
            return rand(181, 240); // 10%: 3 - 4 giờ
        }
    }

    /**
     * Tính discount (thực tế: đa phần không giảm, khách VIP giảm nhẹ)
     */
    private function calcDiscount(float $total, $customer): float
    {
        if (!$customer) {
            return 0;
        }
        // 20% khách có giảm giá (5-10%)
        if (rand(1, 100) <= 20) {
            $rate = rand(5, 10) / 100;
            return round($total * $rate, 0);
        }
        return 0;
    }

    /**
     * Chọn ngẫu nhiên theo trọng số.
     *
     * @param array<int, int> $weights Key là giờ (0-23), value là trọng số
     */
    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $rand  = rand(1, $total);
        $cumulative = 0;
        foreach ($weights as $hour => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $hour;
            }
        }
        return array_key_last($weights);
    }
}
