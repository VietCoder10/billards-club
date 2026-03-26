<?php

namespace App\Repositories\Event;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\UserEvent;

class EventRepository implements EventInterface
{
    private Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function get(Request $request): Collection
    {
        $adminId = Auth::guard('admin')->check() ? Auth::guard('admin')->id() : 1;

        return $this->event
            ->where(function ($q) use ($request) {
                $q->orWhereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            })
            ->with(['users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.avatar');
            }])
            ->where(function ($q) use ($adminId) {
                $q->orWhere('events.created_by', $adminId);
                $q->orWhere('events.private_flag', 0);
                $q->orWhere(function ($q) use ($adminId) {
                    $q->where('events.private_flag', 1)
                        ->whereHas('users', function ($query) use ($adminId) {
                            $query->where('users.id', $adminId);
                        });
                });
            })
            ->get();
    }

    public function store(Request $request): bool
    {
        return DB::transaction(function () use ($request) {
            $adminId = Auth::guard('admin')->check() ? Auth::guard('admin')->id() : 1;

            if ($request->has('id') && $request->id) {
                $event = $this->event->with('users')->find($request->id);
                if (!$event) {
                    return false;
                }
            } else {
                $event = new Event();
                $event->created_by = $adminId;
            }

            if (!$request->has('id') || $event->created_by == $adminId) {
                $event->event_type = $request->event_type ?? 1;
                $event->type = $request->type ?? 1;
                $event->name = $request->name;
                $event->start_date = $request->start_date;
                $event->end_date = $request->end_date;
                $event->note = $request->note;
                $event->color = $request->color;
                if ($request->event_type == 2) {
                    // if target_date exists and this is period
                    // In reference code, target_date = null. Since we don't have target_date in our simplified schema it doesn't matter, but left for safety.
                }

                if (!$event->save()) {
                    return false;
                }
            }

            $flagOwner = false;
            $event->user_events()->whereNotIn('user_id', $request->user_ids ?? [])->delete();

            if ($request->has('user_ids')) {
                foreach ($request->user_ids as $userId) {
                    $userEvent = $event->user_events()->where('user_id', $userId)->first();
                    if (!$userEvent) {
                        $userEvent = new UserEvent;
                        $userEvent->event_id = $event->id;
                        $userEvent->user_id = $userId;
                    }
                    if ($adminId == $userId) {
                        $flagOwner = true;
                    }
                    $userEvent->save();
                }
            }

            if (!$flagOwner) {
                $userEvent = $event->user_events()->where('user_id', $adminId)->first();
                if (!$userEvent) {
                    $userEvent = new UserEvent;
                    $userEvent->event_id = $event->id;
                    $userEvent->user_id = $adminId;
                }
                $userEvent->is_complete = $request->is_complete ? 1 : 0;
                $userEvent->save();
            }

            return true;
        });
    }

    public function destroy(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $event = $this->event->find($id);
            if (!$event) return false;

            $event->users()->detach();
            return $event->delete();
        });
    }
}
