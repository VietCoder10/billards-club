<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Carbon\Carbon;
use App\Repositories\Event\EventInterface;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Dashboard\EventRequest;

class DashboardController extends BaseController
{
    private $event;
    use AuthorizesRequests;

    public function __construct(EventInterface $event)
    {
        $this->event = $event;
    }

    public function index()
    {
        $users = User::select('id', 'name', 'avatar')->get()->map(function ($user) {
            return [
                'label' => $user->name,
                'value' => $user->id,
                'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null
            ];
        });

        $colors = [
            ['label_text' => 'Đỏ', 'value' => '#ef4444'],
            ['label_text' => 'Xanh dương', 'value' => '#3b82f6'],
            ['label_text' => 'Xanh lá', 'value' => '#22c55e'],
            ['label_text' => 'Cam', 'value' => '#f97316'],
            ['label_text' => 'Tím', 'value' => '#a855f7'],
        ];

        return Inertia::render('Admin/Dashboard/Index', [
            'data' => [
                'title' => 'Phân công công việc',
                'users' => $users,
                'colors' => $colors,
                'type' => 1
            ],
            'request' => request()->all()
        ]);
    }

    public function event(Request $request)
    {
        return response()->json([
            'events' => $this->event->get($request)->map(function ($event) {
                $avatars = [];
                $adminId = Auth::guard('admin')->check() ? Auth::guard('admin')->id() : 1;
                $currentUser = $event->users->firstWhere('id', $adminId);
                $isComplete = $currentUser ? $currentUser->pivot->is_complete : 0;
                $userEventUserId = $currentUser ? $currentUser->id : null;

                foreach ($event->users as $user) {
                    $avatars[$user->id] = $user->avatar ? asset('storage/' . $user->avatar) : null;
                }

                return [
                    'id' => $event->id,
                    'title' => $event->name,
                    'backgroundColor' => $isComplete ? '#b9c1c9' : ($event->color ?? '#3b82f6'),
                    'borderColor' => $isComplete ? '#b9c1c9' : ($event->color ?? '#3b82f6'),
                    'display' => 'block',
                    'start' => $event->event_type == 1 ? Carbon::parse($event->start_date)->format('Y-m-d\TH:i:s') : Carbon::parse($event->start_date)->format('Y-m-d'),
                    'end' => $event->event_type == 1 ? Carbon::parse($event->end_date)->format('Y-m-d\TH:i:s') : Carbon::parse($event->end_date)->format('Y-m-d'),
                    'allDay' => $event->event_type == 2,
                    'textColor' => '#fff',
                    'avatar_urls' => array_values($avatars),
                    'extendedProps' => [
                        'event_type' => $event->event_type,
                        'location' => $event->location,
                        'note' => $event->note,
                        'start_date' => Carbon::parse($event->start_date)->format('Y-m-d H:i:s'),
                        'end_date' => Carbon::parse($event->end_date)->format('Y-m-d H:i:s'),
                        'target_date' => $event->target_date ? Carbon::parse($event->target_date)->format('Y-m-d') : null,
                        'type' => $event->type,
                        'color' => $isComplete ? '#b9c1c9' : $event->color,
                        'private_flag' => $event->private_flag == 1,
                        'desktop_notification_flag' => $event->desktop_notification_flag == 1,
                        'user_ids' => $event->users->pluck('id')->toArray(),
                        'flag_delete' => $event->created_by == $adminId ? true : false,
                        'is_complete' => $isComplete ? true : false,
                        'user_event_user_id' => $userEventUserId,
                    ]
                ];
            }),
        ], 200);
    }

    public function store(EventRequest $request)
    {
        $this->authorize('viewAny', User::class);

        if ($this->event->store($request)) {
            return response()->json([
                'message' => 'Lưu thành công',
            ], 200);
        }

        return response()->json([
            'message' => 'Lỗi xảy ra.',
        ], 500);
    }

    public function destroy(string $id)
    {
        $this->authorize('viewAny', User::class);
        if ($this->event->destroy($id)) {
            return response()->json([
                'message' => 'Xóa thành công',
            ], 200);
        }

        return response()->json([
            'message' => 'Lỗi xảy ra.',
        ], 500);
    }
}
