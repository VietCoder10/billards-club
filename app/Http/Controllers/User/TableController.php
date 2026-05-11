<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Enums\TableStatus;
use App\Models\Table;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TableController extends BaseController
{
    public function index(Request $request)
    {
        $tables = Table::with('tablePrice')
            ->where('status', TableStatus::AVAILABLE)
            ->orderBy('id')
            ->get()
            ->map(function ($table) {
                return [
                    'id'             => $table->id,
                    'table_name'     => $table->table_name,
                    'price_per_hour' => $table->tablePrice?->price_per_hour,
                    'price_name'     => $table->tablePrice?->price_name,
                ];
            });

        return Inertia::render('User/Table/Index', $this->mergeSession([
            'data' => [
                'title'  => 'Bàn còn trống',
                'tables' => $tables,
            ],
        ]));
    }
}
