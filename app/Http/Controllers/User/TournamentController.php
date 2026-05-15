<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
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

    public function index()
    {
        $tournaments = $this->interface->getActiveTournaments();
        
        // Add flag to check if current customer is already registered
        $customerId = auth('customer')->id();
        $tournaments->map(function ($tournament) use ($customerId) {
            $tournament->is_registered = $tournament->participants()
                ->where('customer_id', $customerId)
                ->exists();
            return $tournament;
        });

        return Inertia::render('User/Tournament/Index', [
            'data' => [
                'title' => 'Giải đấu',
                'tournaments' => $tournaments,
            ]
        ]);
    }

    public function register(Request $request, $id)
    {
        $customerId = auth('customer')->id();
        
        $result = $this->interface->registerParticipant($id, $customerId);
        
        if ($result) {
            $this->setFlash(__('Đăng ký tham gia giải đấu thành công! Chúng tôi sẽ liên hệ để xác nhận.'), 'success');
        } else {
            $this->setFlash(__('Bạn đã đăng ký giải đấu này rồi hoặc có lỗi xảy ra.'), 'error');
        }
        
        return back();
    }

    public function show($id)
    {
        $tournament = $this->interface->getById($id);
        if (!$tournament) {
            return redirect()->route('user.tournament.index');
        }

        $customerId = auth('customer')->id();
        $isRegistered = $tournament->participants()
            ->where('customer_id', $customerId)
            ->exists();

        return Inertia::render('User/Tournament/Show', [
            'data' => [
                'title' => 'Chi tiết giải đấu: ' . $tournament->name,
                'tournament' => $tournament,
                'isRegistered' => $isRegistered
            ]
        ]);
    }
}
