<?php

namespace Database\Seeders;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        
        Tournament::create([
            'name' => 'Giải Billiards Vô Địch 2026',
            'description' => 'Giải đấu chuyên nghiệp dành cho các cơ thủ hàng đầu.',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(10),
            'registration_deadline' => now()->addDays(5),
            'max_participants' => 64,
            'entry_fee' => 500000,
            'prize_pool' => '50.000.000 VNĐ',
            'status' => 1, // Open
            'created_by' => $admin ? $admin->id : null,
        ]);
    }
}
