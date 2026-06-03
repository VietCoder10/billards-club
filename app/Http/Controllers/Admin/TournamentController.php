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
        return Inertia::render('Admin/Tournament/Form', $this->mergeSession([
            'data' => [
                'title' => 'Tạo giải đấu',
                'urlBack' => session()->get('admin.tournament.list')[0] ?? route('admin.tournament.index'),
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
        $tournament = \App\Models\Tournament::find($id);
        if (!$tournament) {
            $this->setFlash(__('Không tìm thấy giải đấu.'), 'error');
            return redirect()->back();
        }

        $participants = \App\Models\TournamentParticipant::where('tournament_id', $id)
            ->where('status', 1)
            ->get();

        if ($participants->count() < 2) {
            $this->setFlash(__('Giải đấu cần tối thiểu 2 tuyển thủ đã duyệt để tạo sơ đồ thi đấu.'), 'error');
            return redirect()->back();
        }

        \App\Models\TournamentMatch::where('tournament_id', $id)->delete();
        \App\Models\TournamentRound::where('tournament_id', $id)->delete();

        $shuffled = $participants->shuffle()->values();

        $round = \App\Models\TournamentRound::create([
            'tournament_id' => $id,
            'round_number' => 1,
            'name' => 'Vòng 1',
        ]);

        for ($i = 0; $i < $shuffled->count(); $i += 2) {
            $player1 = $shuffled[$i];
            $player2 = isset($shuffled[$i + 1]) ? $shuffled[$i + 1] : null;

            $matchData = [
                'tournament_id' => $id,
                'round_id' => $round->id,
                'player1_id' => $player1->id,
                'player2_id' => $player2 ? $player2->id : null,
                'player1_score' => 0,
                'player2_score' => 0,
                'status' => 0,
            ];

            if (!$player2) {
                $matchData['winner_id'] = $player1->id;
                $matchData['status'] = 2; // Bye automatically wins
            }

            \App\Models\TournamentMatch::create($matchData);
        }

        if ($tournament->status == 1) {
            $tournament->update(['status' => 2]); // Ongoing
        }

        $this->setFlash(__('Tạo sơ đồ thi đấu thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $id);
    }

    public function generateNextRound(Request $request, $id)
    {
        $tournament = \App\Models\Tournament::find($id);
        if (!$tournament) {
            $this->setFlash(__('Không tìm thấy giải đấu.'), 'error');
            return redirect()->back();
        }

        $latestRound = \App\Models\TournamentRound::where('tournament_id', $id)
            ->orderBy('round_number', 'desc')
            ->first();

        if (!$latestRound) {
            $this->setFlash(__('Chưa có vòng đấu nào được tạo.'), 'error');
            return redirect()->back();
        }

        $unfinished = \App\Models\TournamentMatch::where('round_id', $latestRound->id)
            ->where('status', '!=', 2)
            ->exists();

        if ($unfinished) {
            $this->setFlash(__('Vui lòng hoàn thành tất cả các trận đấu ở vòng hiện tại trước khi sinh vòng tiếp theo.'), 'error');
            return redirect()->back();
        }

        $winners = \App\Models\TournamentMatch::where('round_id', $latestRound->id)
            ->orderBy('id', 'asc')
            ->pluck('winner_id')
            ->filter()
            ->values();

        if ($winners->count() === 0) {
            $this->setFlash(__('Không tìm thấy người chiến thắng nào từ vòng trước.'), 'error');
            return redirect()->back();
        }

        if ($winners->count() === 1) {
            $tournament->update(['status' => 3]); // Completed
            $this->setFlash(__('Giải đấu đã tìm ra nhà vô địch và kết thúc!'), 'success');
            return redirect()->route('admin.tournament.show', $id);
        }

        $nextRoundNumber = $latestRound->round_number + 1;
        $roundName = 'Vòng ' . $nextRoundNumber;
        if ($winners->count() <= 2) {
            $roundName = 'Chung kết';
        } elseif ($winners->count() <= 4) {
            $roundName = 'Bán kết';
        } elseif ($winners->count() <= 8) {
            $roundName = 'Tứ kết';
        }

        $nextRound = \App\Models\TournamentRound::create([
            'tournament_id' => $id,
            'round_number' => $nextRoundNumber,
            'name' => $roundName,
        ]);

        for ($i = 0; $i < $winners->count(); $i += 2) {
            $player1Id = $winners[$i];
            $player2Id = isset($winners[$i + 1]) ? $winners[$i + 1] : null;

            $matchData = [
                'tournament_id' => $id,
                'round_id' => $nextRound->id,
                'player1_id' => $player1Id,
                'player2_id' => $player2Id,
                'player1_score' => 0,
                'player2_score' => 0,
                'status' => 0,
            ];

            if (!$player2Id) {
                $matchData['winner_id'] = $player1Id;
                $matchData['status'] = 2; // Bye automatically wins
            }

            \App\Models\TournamentMatch::create($matchData);
        }

        $this->setFlash(__('Sinh vòng đấu tiếp theo thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $id);
    }

    public function resetBracket(Request $request, $id)
    {
        $tournament = \App\Models\Tournament::find($id);
        if (!$tournament) {
            $this->setFlash(__('Không tìm thấy giải đấu.'), 'error');
            return redirect()->back();
        }

        \App\Models\TournamentMatch::where('tournament_id', $id)->delete();
        \App\Models\TournamentRound::where('tournament_id', $id)->delete();

        if ($tournament->status == 2) {
            $tournament->update(['status' => 1]); // Revert to Open
        }

        $this->setFlash(__('Đã xóa toàn bộ sơ đồ thi đấu và đặt lại trạng thái.'), 'success');
        return redirect()->route('admin.tournament.show', $id);
    }

    public function storeMatch(Request $request, $tournamentId)
    {
        $request->validate([
            'round_name' => 'required|string|max:255',
            'player1_id' => 'required|exists:tournament_participants,id',
            'player2_id' => 'required|exists:tournament_participants,id|different:player1_id',
        ], [
            'player1_id.required' => 'Vui lòng chọn tuyển thủ 1.',
            'player2_id.required' => 'Vui lòng chọn tuyển thủ 2.',
            'player2_id.different' => 'Tuyển thủ 2 phải khác tuyển thủ 1.',
        ]);

        $round = \App\Models\TournamentRound::firstOrCreate([
            'tournament_id' => $tournamentId,
            'round_number' => 1,
        ], [
            'name' => $request->round_name,
        ]);

        \App\Models\TournamentMatch::create([
            'tournament_id' => $tournamentId,
            'round_id' => $round->id,
            'player1_id' => $request->player1_id,
            'player2_id' => $request->player2_id,
            'player1_score' => 0,
            'player2_score' => 0,
            'status' => 0,
        ]);

        $this->setFlash(__('Tạo trận đấu thủ công thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $tournamentId);
    }

    public function updateMatch(Request $request, $tournamentId, $matchId)
    {
        $match = \App\Models\TournamentMatch::findOrFail($matchId);

        $request->validate([
            'player1_score' => 'required|integer|min:0',
            'player2_score' => 'required|integer|min:0',
            'status' => 'required|integer|in:0,1,2',
            'winner_id' => 'nullable|exists:tournament_participants,id',
        ]);

        $data = $request->only(['player1_score', 'player2_score', 'status', 'winner_id']);

        if ($data['status'] == 2 && !$data['winner_id']) {
            if ($data['player1_score'] > $data['player2_score']) {
                $data['winner_id'] = $match->player1_id;
            } elseif ($data['player2_score'] > $data['player1_score']) {
                $data['winner_id'] = $match->player2_id;
            }
        }

        $match->update($data);

        $this->setFlash(__('Cập nhật kết quả trận đấu thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $tournamentId);
    }

    public function destroyMatch(Request $request, $tournamentId, $matchId)
    {
        $match = \App\Models\TournamentMatch::findOrFail($matchId);
        $match->delete();

        $this->setFlash(__('Xóa trận đấu thành công.'), 'success');
        return redirect()->route('admin.tournament.show', $tournamentId);
    }
}
