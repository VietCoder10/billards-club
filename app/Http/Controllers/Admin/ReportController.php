<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Invoice;
use Carbon\Carbon;

class ReportController extends BaseController
{
    /**
     * Display the revenue report dashboard.
     */
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', Carbon::now()->month);
        $selectedYear = $request->input('year', Carbon::now()->year);

        $selectedMonth = intval($selectedMonth);
        $selectedYear = intval($selectedYear);

        $isCurrentMonth = ($selectedMonth === Carbon::now()->month) && ($selectedYear === Carbon::now()->year);

        $selectedDate = Carbon::create($selectedYear, $selectedMonth, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        $startOfYear = $selectedDate->copy()->startOfYear();
        $endOfYear = $selectedDate->copy()->endOfYear();

        $today = Carbon::today();
        
        $revenueToday = Invoice::whereDate('created_at', $today)->sum('final_amount');
        $revenueThisMonth = Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('final_amount');
        $revenueThisYear = Invoice::whereBetween('created_at', [$startOfYear, $endOfYear])->sum('final_amount');

        // Revenue by day for the selected month
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

        return Inertia::render('Admin/Report/Index', [
            'data' => [
                'title' => 'Báo cáo doanh thu',
                'revenueToday' => $revenueToday,
                'revenueThisMonth' => $revenueThisMonth,
                'revenueThisYear' => $revenueThisYear,
                'dailyRevenue' => array_values($dailyRevenue),
                'days' => array_keys($dailyRevenue),
                'selectedMonth' => $selectedMonth,
                'selectedYear' => $selectedYear,
                'isCurrentMonth' => $isCurrentMonth,
            ]
        ]);
    }
}
