<?php

namespace App\Repositories\Report;

use App\Enums\OrderStatus;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Table;
use Carbon\Carbon;

class ReportRepository implements ReportInterface
{
    public function getRevenueByDate($date)
    {
        return Invoice::whereDate('created_at', $date)->sum('final_amount');
    }

    public function getRevenueByRange($startDate, $endDate)
    {
        return Invoice::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
    }

    public function getDailyRevenue($month, $year)
    {
        $selectedDate = Carbon::create($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        $daysInMonth = $selectedDate->daysInMonth;
        $dailyRevenue = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dailyRevenue[$i] = 0;
        }

        $invoicesThisMonth = Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(created_at) as date, SUM(final_amount) as total')
            ->groupBy('date')
            ->get();

        foreach ($invoicesThisMonth as $invoice) {
            $day = Carbon::parse($invoice->date)->day;
            $dailyRevenue[$day] = (float)$invoice->total;
        }

        return $dailyRevenue;
    }

    public function getTableRevenue($startDate, $endDate): float
    {
        return (float) Order::where('status', OrderStatus::COMPLETED)
            ->whereBetween('ended_at', [$startDate, $endDate])
            ->sum('table_total');
    }

    public function getServiceRevenue($startDate, $endDate): float
    {
        return (float) Order::where('status', OrderStatus::COMPLETED)
            ->whereBetween('ended_at', [$startDate, $endDate])
            ->sum('service_total');
    }

    public function getTotalRevenue($startDate, $endDate): float
    {
        return (float) Order::where('status', OrderStatus::COMPLETED)
            ->whereBetween('ended_at', [$startDate, $endDate])
            ->sum('final_total');
    }

    public function getOrderCount($startDate, $endDate): int
    {
        return Order::where('status', OrderStatus::COMPLETED)
            ->whereBetween('ended_at', [$startDate, $endDate])
            ->count();
    }

    public function getTotalMinutes($startDate, $endDate): int
    {
        return (int) Order::where('status', OrderStatus::COMPLETED)
            ->whereBetween('ended_at', [$startDate, $endDate])
            ->sum('total_minutes');
    }

    public function getTotalTableCount(): int
    {
        return Table::count();
    }

    public function getHoursDistribution($startDate, $endDate)
    {
        return Order::where('status', OrderStatus::COMPLETED)
            ->whereBetween('ended_at', [$startDate, $endDate])
            ->selectRaw('HOUR(started_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->get();
    }

    public function getTopTables($startDate, $endDate, int $limit = 3)
    {
        return Order::where('orders.status', OrderStatus::COMPLETED)
            ->whereBetween('orders.ended_at', [$startDate, $endDate])
            ->join('tables', 'orders.table_id', '=', 'tables.id')
            ->selectRaw('tables.table_name, COUNT(*) as count, SUM(orders.total_minutes) as minutes')
            ->groupBy('tables.table_name')
            ->orderByDesc('count')
            ->take($limit)
            ->get();
    }

    public function getTopServices($startDate, $endDate, int $limit = 3)
    {
        return OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.status', OrderStatus::COMPLETED)
            ->whereBetween('orders.ended_at', [$startDate, $endDate])
            ->whereNotNull('order_details.product_id')
            ->selectRaw('order_details.product_name, SUM(order_details.quantity) as total_qty')
            ->groupBy('order_details.product_name')
            ->orderByDesc('total_qty')
            ->take($limit)
            ->get();
    }

    public function getTopCustomers($startDate, $endDate, int $limit = 3)
    {
        return Invoice::whereBetween('invoices.created_at', [$startDate, $endDate])
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->selectRaw('customers.name, SUM(invoices.final_amount) as total_spent')
            ->groupBy('customers.name')
            ->orderByDesc('total_spent')
            ->take($limit)
            ->get();
    }
}
