<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = \Illuminate\Support\Facades\Hash::make('Laravel@2025');
        $tournament = \App\Models\Tournament::first(); // Lấy giải đấu đầu tiên vừa tạo từ TournamentSeeder
        
        for ($i = 0; $i < 64; $i++) {
            $emailPrefix = $this->getNameFromNumber($i);
            $customer = Customer::create([
                'name' => 'Tuyển thủ ' . $emailPrefix,
                'email' => strtolower($emailPrefix) . '@test.com',
                'password' => $password,
                'phone' => '09' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
            ]);

            if ($tournament) {
                \App\Models\TournamentParticipant::create([
                    'tournament_id' => $tournament->id,
                    'customer_id' => $customer->id,
                    'status' => 1, // Approved
                ]);
            }
        }
    }

    /**
     * Convert number to alphabet sequence (0 => A, 25 => Z, 26 => AA, etc.)
     */
    private function getNameFromNumber($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }
}
