<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('Laravel2025'),
                'user_role' => UserRole::ADMIN,
            ]
        );
        // Staff
        User::updateOrCreate(
            ['email' => 'staff@gmail.com'],
            [
                'name' => 'staff',
                'password' => Hash::make('Laravel2025'),
                'user_role' => UserRole::USER,
            ]
        );
    }
}
