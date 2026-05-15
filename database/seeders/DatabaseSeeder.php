<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        User::factory(10)->create(); // Giảm xuống cho nhanh
        $this->call(SupplierSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(TablePriceMasterSeeder::class);
        $this->call(TableSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(InvoiceSeeder::class);
        
        // Tournament System
        $this->call(TournamentSeeder::class);
        $this->call(CustomerSeeder::class);
    }
}
