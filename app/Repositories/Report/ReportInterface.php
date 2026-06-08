<?php

namespace App\Repositories\Report;

interface ReportInterface
{
    public function getRevenueByDate($date);
    public function getRevenueByRange($startDate, $endDate);
    public function getDailyRevenue($month, $year);

    // AI Report methods
    public function getTableRevenue($startDate, $endDate): float;
    public function getServiceRevenue($startDate, $endDate): float;
    public function getTotalRevenue($startDate, $endDate): float;
    public function getOrderCount($startDate, $endDate): int;
    public function getTotalMinutes($startDate, $endDate): int;
    public function getTotalTableCount(): int;
    public function getHoursDistribution($startDate, $endDate);
    public function getTopTables($startDate, $endDate, int $limit = 3);
    public function getTopServices($startDate, $endDate, int $limit = 3);
    public function getTopCustomers($startDate, $endDate, int $limit = 3);
}
