<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\UserEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Tạo phân công ca làm việc cho toàn bộ tháng 6/2026
     *
     * Ca đêm  : 00:00 - 06:00
     * Ca sáng : 06:00 - 12:00
     * Ca chiều: 12:00 - 18:00
     * Ca tối  : 18:00 - 24:00
     */
    public function run(): void
    {
        // Lấy danh sách nhân viên (event_type = 1: phân công ca)
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('Không có user nào trong database. Hãy chạy UserSeeder trước.');
            return;
        }

        // Định nghĩa 4 ca làm việc
        $shifts = [
            [
                'name'       => 'Ca đêm',
                'start_hour' => 0,
                'end_hour'   => 6,
                'color'      => '#6366f1', // indigo
                'note'       => 'Ca đêm: 00:00 - 06:00',
            ],
            [
                'name'       => 'Ca sáng',
                'start_hour' => 6,
                'end_hour'   => 12,
                'color'      => '#f59e0b', // amber
                'note'       => 'Ca sáng: 06:00 - 12:00',
            ],
            [
                'name'       => 'Ca chiều',
                'start_hour' => 12,
                'end_hour'   => 18,
                'color'      => '#10b981', // emerald
                'note'       => 'Ca chiều: 12:00 - 18:00',
            ],
            [
                'name'       => 'Ca tối',
                'start_hour' => 18,
                'end_hour'   => 24,
                'color'      => '#ef4444', // red
                'note'       => 'Ca tối: 18:00 - 00:00 (hôm sau)',
            ],
        ];

        // Admin (người tạo sự kiện) - ưu tiên user có role ADMIN (1)
        $admin = User::where('user_role', \App\Enums\UserRole::ADMIN)->first()
            ?? $users->first();

        $year  = 2026;
        $month = 6;
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        $totalCreated = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);

            foreach ($shifts as $shift) {
                $startHour = $shift['start_hour'];
                $endHour   = $shift['end_hour'];

                // Ca tối kết thúc lúc 00:00 hôm sau
                if ($endHour === 24) {
                    $startDate = $date->copy()->setTime($startHour, 0, 0);
                    $endDate   = $date->copy()->addDay()->setTime(0, 0, 0);
                } else {
                    $startDate = $date->copy()->setTime($startHour, 0, 0);
                    $endDate   = $date->copy()->setTime($endHour, 0, 0);
                }

                // Tạo event cho ca làm việc
                $event = Event::create([
                    'event_type'               => 1,          // 1: phân công ca
                    'name'                     => $shift['name'] . ' - ' . $date->format('d/m/Y'),
                    'location'                 => 'Billiards Club',
                    'start_date'               => $startDate,
                    'end_date'                 => $endDate,
                    'target_date'              => $date->toDateString(),
                    'note'                     => $shift['note'],
                    'color'                    => $shift['color'],
                    'type'                     => 1,
                    'desktop_notification_flag' => 0,
                    'private_flag'             => 0,
                    'created_by'               => $admin->id,
                ]);

                // Phân công nhân viên vào ca (round-robin theo ca)
                // Ca đêm & ca sáng: ít nhân viên hơn; ca chiều & ca tối: đông hơn
                $assignCount = match ($shift['name']) {
                    'Ca đêm'  => max(1, min(2, $users->count())),
                    'Ca sáng' => max(1, min(3, $users->count())),
                    'Ca chiều' => max(1, min(3, $users->count())),
                    'Ca tối'  => max(1, min(2, $users->count())),
                    default   => 1,
                };

                // Chọn nhân viên theo vòng tròn để đảm bảo phân công đều
                $shiftIndex  = array_search($shift, $shifts);
                $dayShiftKey = ($day - 1) * count($shifts) + $shiftIndex;

                $assignedUsers = $users->slice(
                    ($dayShiftKey * $assignCount) % $users->count(),
                    $assignCount
                )->values();

                // Nếu slice không đủ, bổ sung từ đầu
                if ($assignedUsers->count() < $assignCount) {
                    $remaining = $assignCount - $assignedUsers->count();
                    $extra     = $users->slice(0, $remaining)->values();
                    $assignedUsers = $assignedUsers->merge($extra);
                }

                foreach ($assignedUsers as $user) {
                    UserEvent::create([
                        'event_id'    => $event->id,
                        'user_id'     => $user->id,
                        'memo'        => 'Phân công ' . $shift['name'] . ' ngày ' . $date->format('d/m/Y'),
                        'is_complete' => $date->isPast() ? 1 : 0,
                    ]);
                }

                $totalCreated++;
            }
        }

        $this->command->info("✅ Đã tạo {$totalCreated} ca làm việc cho tháng 6/{$year}.");
        $this->command->info("   (4 ca/ngày × {$daysInMonth} ngày = " . (4 * $daysInMonth) . " ca)");
    }
}
