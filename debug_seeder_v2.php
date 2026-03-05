<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\DB;

DB::table('products')->delete();

try {
    $seeder = new ProductSeeder();
    $seeder->run();
    echo "ProductSeeder ran successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
