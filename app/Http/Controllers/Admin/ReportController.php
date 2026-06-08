<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Repositories\Report\ReportInterface;
use Illuminate\Support\Facades\Http;

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

    /**
     * Generate AI business report based on real-time database metrics.
     */
    public function generateAIReport(Request $request)
    {
        $timeRange  = $request->input('time_range', 'this_month');
        $startDateStr = $request->input('start_date');
        $endDateStr   = $request->input('end_date');

        // Determine date range
        switch ($timeRange) {
            case 'today':
                $startDate   = Carbon::today()->startOfDay();
                $endDate     = Carbon::today()->endOfDay();
                $reportPeriod = 'Hôm nay (' . Carbon::today()->format('d/m/Y') . ')';
                $prevStartDate = Carbon::yesterday()->startOfDay();
                $prevEndDate   = Carbon::yesterday()->endOfDay();
                break;

            case 'this_week':
                $startDate   = Carbon::now()->startOfWeek()->startOfDay();
                $endDate     = Carbon::now()->endOfWeek()->endOfDay();
                $reportPeriod = 'Tuần này (Từ ' . $startDate->format('d/m/Y') . ' đến ' . $endDate->format('d/m/Y') . ')';
                $prevStartDate = $startDate->copy()->subWeek()->startOfDay();
                $prevEndDate   = $endDate->copy()->subWeek()->endOfDay();
                break;

            case 'this_month':
                $startDate   = Carbon::now()->startOfMonth()->startOfDay();
                $endDate     = Carbon::now()->endOfMonth()->endOfDay();
                $reportPeriod = 'Tháng này (Từ ' . $startDate->format('d/m/Y') . ' đến ' . $endDate->format('d/m/Y') . ')';
                $prevStartDate = $startDate->copy()->subMonth()->startOfDay();
                $prevEndDate   = $endDate->copy()->subMonth()->endOfDay();
                break;

            case 'custom':
                if (empty($startDateStr) || empty($endDateStr)) {
                    return response()->json([
                        'error' => 'Vui lòng chọn khoảng thời gian bắt đầu và kết thúc đầy đủ.'
                    ], 400);
                }
                $startDate   = Carbon::parse($startDateStr)->startOfDay();
                $endDate     = Carbon::parse($endDateStr)->endOfDay();
                $reportPeriod = 'Tùy chỉnh (Từ ' . $startDate->format('d/m/Y') . ' đến ' . $endDate->format('d/m/Y') . ')';
                $diffInDays    = $startDate->diffInDays($endDate) + 1;
                $prevStartDate = $startDate->copy()->subDays($diffInDays)->startOfDay();
                $prevEndDate   = $endDate->copy()->subDays($diffInDays)->endOfDay();
                break;

            default:
                $startDate   = Carbon::now()->startOfMonth()->startOfDay();
                $endDate     = Carbon::now()->endOfMonth()->endOfDay();
                $reportPeriod = 'Tháng này';
                $prevStartDate = $startDate->copy()->subMonth()->startOfDay();
                $prevEndDate   = $endDate->copy()->subMonth()->endOfDay();
                break;
        }

        // Collect all metrics from repository
        $tableRevenue   = $this->report->getTableRevenue($startDate, $endDate);
        $serviceRevenue = $this->report->getServiceRevenue($startDate, $endDate);
        $totalRevenue   = $this->report->getTotalRevenue($startDate, $endDate);
        $orderCount     = $this->report->getOrderCount($startDate, $endDate);
        $totalMinutes   = $this->report->getTotalMinutes($startDate, $endDate);
        $totalHours     = round($totalMinutes / 60, 1);

        $totalTables        = $this->report->getTotalTableCount();
        $daysDiff           = $startDate->diffInDays($endDate) + 1;
        $totalCapacityHours = $totalTables * 24 * $daysDiff;
        $tableUsageRate     = $totalCapacityHours > 0 ? round(($totalHours / $totalCapacityHours) * 100, 1) : 0;

        $hoursDistribution = $this->report->getHoursDistribution($startDate, $endDate);
        if ($hoursDistribution->isNotEmpty()) {
            $busyHours  = $hoursDistribution->take(2)->pluck('hour')
                ->map(fn($h) => "{$h}h - " . ($h + 1) . 'h')->implode(', ');
            $quietHours = $hoursDistribution->reverse()->take(2)->pluck('hour')
                ->map(fn($h) => "{$h}h - " . ($h + 1) . 'h')->implode(', ');
        } else {
            $busyHours  = 'Không có số liệu';
            $quietHours = 'Không có số liệu';
        }

        $topTablesData = $this->report->getTopTables($startDate, $endDate);
        $topTables = $topTablesData->isNotEmpty()
            ? $topTablesData->map(fn($t) => "Bàn {$t->table_name} ({$t->count} lượt, " . round($t->minutes / 60, 1) . ' giờ)')->implode(', ')
            : 'Không có số liệu';

        $topServicesData = $this->report->getTopServices($startDate, $endDate);
        $topServices = $topServicesData->isNotEmpty()
            ? $topServicesData->map(fn($p) => "{$p->product_name} ({$p->total_qty} phần)")->implode(', ')
            : 'Không có số liệu';

        $topCustomersData = $this->report->getTopCustomers($startDate, $endDate);
        $topCustomers = $topCustomersData->isNotEmpty()
            ? $topCustomersData->map(fn($c) => "{$c->name} (" . number_format($c->total_spent, 0, ',', '.') . ' VNĐ)')->implode(', ')
            : 'Không có số liệu';

        $prevRevenue        = $this->report->getTotalRevenue($prevStartDate, $prevEndDate);
        $revenueDiff        = $totalRevenue - $prevRevenue;
        $revenueDiffPercent = $prevRevenue > 0 ? round(($revenueDiff / $prevRevenue) * 100, 1) : 0;
        $compareText        = 'Kỳ trước liền kề đạt ' . number_format($prevRevenue, 0, ',', '.') . ' VNĐ. ';
        if ($revenueDiff > 0) {
            $compareText .= 'Tăng trưởng +' . number_format($revenueDiff, 0, ',', '.') . ' VNĐ (+' . $revenueDiffPercent . '%).';
        } elseif ($revenueDiff < 0) {
            $compareText .= 'Sụt giảm ' . number_format(abs($revenueDiff), 0, ',', '.') . ' VNĐ (' . $revenueDiffPercent . '%).';
        } else {
            $compareText .= 'Bằng kỳ trước.';
        }

        // Build prompt
        $prompt = "
Vai trò: Bạn là chuyên gia phân tích dữ liệu và tư vấn kinh doanh trong lĩnh vực billiards.
Nhiệm vụ: Dựa trên các số liệu được cung cấp dưới đây, hãy tạo một báo cáo kinh doanh chuyên nghiệp dành cho chủ quán billiards.
Yêu cầu:
- Phân tích dựa hoàn toàn trên dữ liệu đầu vào.
- Đưa ra nhận xét khách quan và dễ hiểu.
- Xác định các xu hướng nổi bật trong hoạt động kinh doanh.
- Chỉ ra các vấn đề cần lưu ý nếu có.
- Đưa ra các đề xuất thực tế giúp tăng doanh thu và nâng cao hiệu quả vận hành.
- Văn phong chuyên nghiệp, ngắn gọn nhưng đầy đủ thông tin.
- Không sử dụng các cụm từ như \"Tôi là AI\" hoặc \"Theo dữ liệu được cung cấp\".
- Trả kết quả bằng tiếng Việt.

Dữ liệu đầu vào:
Kỳ báo cáo: {$reportPeriod}
Tổng doanh thu: " . number_format($totalRevenue, 0, ',', '.') . " VNĐ
Doanh thu tiền bàn: " . number_format($tableRevenue, 0, ',', '.') . " VNĐ
Doanh thu dịch vụ: " . number_format($serviceRevenue, 0, ',', '.') . " VNĐ
Số lượng đơn hàng: {$orderCount}
Số lượt khách: {$orderCount}
Số giờ sử dụng bàn: {$totalHours} giờ
Tỷ lệ sử dụng bàn trung bình: {$tableUsageRate}%
Khung giờ đông khách: {$busyHours}
Khung giờ vắng khách: {$quietHours}
Top bàn được sử dụng nhiều nhất: {$topTables}
Top dịch vụ bán chạy: {$topServices}
Top khách hàng chi tiêu cao: {$topCustomers}
Dữ liệu so sánh kỳ trước: {$compareText}

Định dạng kết quả trả về bắt buộc theo đúng cấu trúc sau:
1. Tổng quan hoạt động
[Nội dung tóm tắt ngắn gọn tình hình kinh doanh]
2. Phân tích doanh thu
[Đánh giá doanh thu tổng thể, cơ cấu doanh thu giữa tiền bàn và dịch vụ]
3. Phân tích khách hàng
[Đánh giá lượng khách, hành vi và mức chi tiêu của nhóm khách hàng]
4. Phân tích hiệu suất sử dụng bàn
[Đánh giá tỷ lệ sử dụng bàn, bàn hoạt động hiệu quả và giờ cao điểm]
5. Điểm mạnh nổi bật
[Các kết quả tích cực đạt được]
6. Các vấn đề cần cải thiện
[Hạn chế hoặc các dấu hiệu cần chú ý]
7. Đề xuất cải thiện
[Ít nhất 5 đề xuất cụ thể nhằm tăng doanh thu, hiệu suất, trải nghiệm và tối ưu vận hành]
8. Kết luận
[Tóm tắt tình hình hoạt động và triển vọng kinh doanh sắp tới]
";

        // Call Gemini API
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json([
                'error' => 'Vui lòng cấu hình GEMINI_API_KEY trong tệp .env để sử dụng tính năng này.'
            ], 400);
        }

        $model = env('GEMINI_MODEL', 'gemini-2.5-flash');
        $url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        try {
            $response = Http::timeout(60)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Không thể kết nối đến máy chủ AI: ' . $response->body()
                ], 500);
            }

            $result        = $response->json();
            $generatedText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$generatedText) {
                return response()->json([
                    'error' => 'Không nhận được nội dung trả về từ AI. Phản hồi: ' . json_encode($result)
                ], 500);
            }

            return response()->json([
                'report'  => $generatedText,
                'metrics' => [
                    'total_revenue'   => $totalRevenue,
                    'table_revenue'   => $tableRevenue,
                    'service_revenue' => $serviceRevenue,
                    'order_count'     => $orderCount,
                    'table_hours'     => $totalHours,
                    'usage_rate'      => $tableUsageRate,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã xảy ra lỗi khi tạo báo cáo AI: ' . $e->getMessage()
            ], 500);
        }
    }
}
