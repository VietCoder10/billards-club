<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Enums\StatusCode;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\Tournament\StoreTournamentRequest;
use App\Repositories\Tournament\TournamentInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TournamentController extends BaseController
{
    private TournamentInterface $interface;

    public function __construct(TournamentInterface $interface)
    {
        $this->interface = $interface;
    }

    public function index(Request $request): \Inertia\Response
    {
        $tournaments = $this->interface->get($request);
        session()->forget('admin.tournament.list');
        session()->push('admin.tournament.list', url()->full());

        return Inertia::render('Admin/Tournament/Index', $this->mergeSession([
            'data' => [
                'title' => 'Quản lý giải đấu',
                'tournaments' => $tournaments->items(),
                'sortLinks' => $this->sortLinks('admin.tournament.index', [
                    ['key' => 'name', 'name' => 'Tên giải đấu'],
                    ['key' => 'start_date', 'name' => 'Ngày bắt đầu'],
                    ['key' => 'status', 'name' => 'Trạng thái'],
                ], $request),
                'request' => $request->all(),
                'paginator' => $this->paginator($tournaments->appends(SearchQueryComponent::alterQuery($request))),
            ],
        ]));
    }

    public function create()
    {
        return Inertia::render('Admin/Tournament/Form', [
            'data' => [
                'title' => 'Tạo giải đấu',
                'urlBack' => session()->get('admin.tournament.list')[0] ?? route('admin.tournament.index'),
            ],
        ]);
    }

    public function store(StoreTournamentRequest $request)
    {
        if ($this->interface->store($request)) {
            $this->setFlash(__('Tạo giải đấu thành công.'), 'success');
            return redirect()->route('admin.tournament.index');
        }
        $this->setFlash(__('Tạo giải đấu thất bại.'), 'error');
        return redirect()->route('admin.tournament.create');
    }

    public function show(string $id)
    {
        $tournament = $this->interface->getById($id);
        if (!$tournament) {
            $this->setFlash(__('Không tìm thấy giải đấu.'), 'error');
            return redirect()->route('admin.tournament.index');
        }
        $participants = \App\Models\TournamentParticipant::with('customer')
            ->where('tournament_id', $id)
            ->get();

        return Inertia::render('Admin/Tournament/Show', [
            'data' => [
                'title' => 'Chi tiết giải đấu: ' . $tournament->name,
                'tournament' => $tournament,
                'participants' => $participants,
                'urlBack' => session()->get('admin.tournament.list')[0] ?? route('admin.tournament.index'),
            ]
        ]);
    }

    public function edit(string $id)
    {
        $tournament = $this->interface->getById($id);
        if (!$tournament) {
            $this->setFlash(__('Không tìm thấy giải đấu.'), 'error');
            return redirect()->route('admin.tournament.index');
        }

        return Inertia::render('Admin/Tournament/Form', [
            'data' => [
                'title' => 'Cập nhật giải đấu',
                'tournament' => $tournament,
                'isEdit' => true,
                'urlBack' => session()->get('admin.tournament.list')[0] ?? route('admin.tournament.index'),
            ],
        ]);
    }

    public function update(StoreTournamentRequest $request, string $id)
    {
        if ($this->interface->update($request, $id)) {
            $this->setFlash(__('Cập nhật giải đấu thành công.'));
            return redirect()->route('admin.tournament.index');
        }
        $this->setFlash(__('Cập nhật giải đấu thất bại.'), 'error');
        return redirect()->route('admin.tournament.edit', $id);
    }

    public function destroy(string $id)
    {
        if ($this->interface->destroy($id)) {
            return response()->json([
                'message' => 'Xóa giải đấu thành công.',
            ], StatusCode::OK);
        }

        return response()->json([
            'message' => 'Xóa giải đấu thất bại.',
        ], StatusCode::INTERNAL_ERR);
    }

    // Participants management
    public function updateParticipantStatus(Request $request, $id, $participantId)
    {
        if ($this->interface->updateParticipantStatus($participantId, $request->status)) {
            $this->setFlash(__('Cập nhật trạng thái thành công.'), 'success');
        } else {
            $this->setFlash(__('Cập nhật thất bại.'), 'error');
        }
        return back();
    }
}
