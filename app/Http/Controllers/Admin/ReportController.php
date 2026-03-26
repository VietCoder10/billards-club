<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Repositories\Report\ReportInterface;

class ReportController extends BaseController
{
    private ReportInterface $report;

    public function __construct(ReportInterface $reportRepository)
    {
        $this->report = $reportRepository;
    }

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

        $revenueToday = $this->report->getRevenueByDate($today);
        $revenueThisMonth = $this->report->getRevenueByRange($startOfMonth, $endOfMonth);
        $revenueThisYear = $this->report->getRevenueByRange($startOfYear, $endOfYear);

        $dailyRevenue = $this->report->getDailyRevenue($selectedMonth, $selectedYear);

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
