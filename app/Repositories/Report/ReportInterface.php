<?php

namespace App\Repositories\Report;

interface ReportInterface
{
    public function getRevenueByDate($date);
    public function getRevenueByRange($startDate, $endDate);
    public function getDailyRevenue($month, $year);
}
