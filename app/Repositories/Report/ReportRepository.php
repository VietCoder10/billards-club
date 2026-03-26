<?php

namespace App\Repositories\Report;

use App\Models\Invoice;
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
}
