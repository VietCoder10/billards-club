<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Enums\StatusCode;
use App\Enums\TournamentMatchStatus;
use App\Enums\TournamentParticipantStatus;
use App\Enums\TournamentStatus;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\Tournament\StoreTournamentMatchRequest;
use App\Http\Requests\Admin\Tournament\StoreTournamentRequest;
use App\Http\Requests\Admin\Tournament\UpdateTournamentMatchRequest;
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
        return Inertia::render('Admin/Tournament/Form', $this->mergeSession([
            'data' => [
                'title' => 'Tạo giải đấu',
                'urlBack' => session()->get('admin.tournament.list')[0] ?? route('admin.tournament.index'),
                'statusOptions' => TournamentStatus::getOptions(),
            ],
        ]));
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

        $rounds = \App\Models\TournamentRound::with([
            'matches.player1.customer',
            'matches.player2.customer',
            'matches.winner.customer',
        ])
            ->where('tournament_id', $id)
            ->orderBy('round_number', 'asc')
            ->get();

        return Inertia::render('Admin/Tournament/Show', $this->mergeSession([
            'data' => [
                'title' => 'Chi tiết giải đấu: ' . $tournament->name,
                'tournament' => $tournament,
                'participants' => $participants,
                'rounds' => $rounds,
                'urlBack' => session()->get('admin.tournament.list')[0] ?? route('admin.tournament.index'),
                'tournamentStatusOptions' => TournamentStatus::getOptions(),
                'matchStatusOptions' => TournamentMatchStatus::getOptions(),
                'participantStatusOptions' => TournamentParticipantStatus::getOptions(),
            ]
        ]));
    }

    public function edit(string $id)
    {
        $tournament = $this->interface->getById($id);
        if (!$tournament) {
            $this->setFlash(__('Không tìm thấy giải đấu.'), 'error');
            return redirect()->route('admin.tournament.index');
        }

        return Inertia::render('Admin/Tournament/Form', $this->mergeSession([
            'data' => [
                'title' => 'Cập nhật giải đấu',
                'tournament' => $tournament,
                'isEdit' => true,
                'urlBack' => session()->get('admin.tournament.list')[0] ?? route('admin.tournament.index'),
                'statusOptions' => TournamentStatus::getOptions(),
            ],
        ]));
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
            $participant = \App\Models\TournamentParticipant::with(['customer', 'tournament'])->find($participantId);
            if ($participant && $participant->customer && $participant->customer->email && in_array($request->status, [1, 2])) {
                $data = [
                    'status' => $request->status,
                    'customer_name' => $participant->customer->name,
                    'tournament_name' => $participant->tournament->name,
                    'start_date' => $participant->tournament->start_date,
                ];
                \Illuminate\Support\Facades\Mail::to($participant->customer->email)->send(new \App\Mail\TournamentStatusNotification($data));
            }

            $this->setFlash(__('Cập nhật trạng thái thành công.'), 'success');
        } else {
            $this->setFlash(__('Cập nhật thất bại.'), 'error');
        }
        return redirect()->route('admin.tournament.show', $id);
    }

    // Bracket & Match Management
    public function generateBracket(Request $request, $id)
    {
        $result = $this->interface->generateBracket($id);

        if (!$result['success'] && $result['error'] === 'not_found') {
            $this->setFlash(__('Không tìm thấy giải đấu.'), 'error');
            return redirect()->back();
        }

        if (!$result['success'] && $result['error'] === 'not_enough_participants') {
            $this->setFlash(__('Giải đấu cần tối thiểu 2 tuyển thủ đã duyệt để tạo sơ đồ thi đấu.'), 'error');
            return redirect()->back();
        }

        $this->setFlash(__('Tạo sơ đồ thi đấu thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $id);
    }

    public function generateNextRound(Request $request, $id)
    {
        $result = $this->interface->generateNextRound($id);

        if (!$result['success'] && $result['error'] === 'not_found') {
            $this->setFlash(__('Không tìm thấy giải đấu.'), 'error');
            return redirect()->back();
        }

        if (!$result['success'] && $result['error'] === 'round_not_found') {
            $this->setFlash(__('Chưa có vòng đấu nào được tạo.'), 'error');
            return redirect()->back();
        }

        if (!$result['success'] && $result['error'] === 'unfinished_matches') {
            $this->setFlash(__('Vui lòng hoàn thành tất cả các trận đấu ở vòng hiện tại trước khi sinh vòng tiếp theo.'), 'error');
            return redirect()->back();
        }

        if (!$result['success'] && $result['error'] === 'winner_not_found') {
            $this->setFlash(__('Không tìm thấy người chiến thắng nào từ vòng trước.'), 'error');
            return redirect()->back();
        }

        if ($result['completed'] ?? false) {
            $this->setFlash(__('Giải đấu đã tìm ra nhà vô địch và kết thúc!'), 'success');
            return redirect()->route('admin.tournament.show', $id);
        }

        $this->setFlash(__('Sinh vòng đấu tiếp theo thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $id);
    }

    public function resetBracket(Request $request, $id)
    {
        $result = $this->interface->resetBracket($id);

        if (!$result['success'] && $result['error'] === 'not_found') {
            $this->setFlash(__('Không tìm thấy giải đấu.'), 'error');
            return redirect()->back();
        }

        $this->setFlash(__('Đã xóa toàn bộ sơ đồ thi đấu và đặt lại trạng thái.'), 'success');
        return redirect()->route('admin.tournament.show', $id);
    }

    public function storeMatch(StoreTournamentMatchRequest $request, $tournamentId)
    {
        $this->interface->storeMatch($tournamentId, $request->validated());

        $this->setFlash(__('Tạo trận đấu thủ công thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $tournamentId);
    }

    public function updateMatch(UpdateTournamentMatchRequest $request, $tournamentId, $matchId)
    {
        $data = $request->only(['player1_score', 'player2_score', 'status', 'winner_id']);

        if (!$this->interface->updateMatch($matchId, $data)) {
            $this->setFlash(__('Không tìm thấy trận đấu.'), 'error');
            return redirect()->route('admin.tournament.show', $tournamentId);
        }

        $this->setFlash(__('Cập nhật kết quả trận đấu thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $tournamentId);
    }

    public function destroyMatch(Request $request, $tournamentId, $matchId)
    {
        if (!$this->interface->destroyMatch($tournamentId, $matchId)) {
            $this->setFlash(__('Không tìm thấy trận đấu.'), 'error');
            return redirect()->route('admin.tournament.show', $tournamentId);
        }

        $this->setFlash(__('Xóa trận đấu thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $tournamentId);
    }
}
