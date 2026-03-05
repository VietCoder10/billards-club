<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Http\Controllers\BaseController;
use App\Repositories\Tables\TableInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TableController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    private TableInterface $table;
    public function __construct(TableInterface $table)
    {
        $this->table = $table;
    }
    public function index(Request $request)
    {
        //
        $tables = $this->table->get($request);
        session()->forget('admin.table.list');
        session()->push('admin.table.list', url()->full());
        return Inertia::render('Admin/Table/Index', $this->mergeSession([
            'data' => [
                'title' => 'Danh sách bàn',
                'tables' => $tables->items(),
                'sortLinks' => $this->sortLinks('admin.table.index', [
                    ['key' => 'table_name', 'name' => 'Tên bàn'],
                    ['key' => 'status', 'name' => 'Trạng thái'],
                    ['key' => 'price_per_hour', 'name' => 'Giá theo giờ'],
                ], $request),
                'request' => $request->all(),
                'paginator' => $this->paginator($tables->appends(SearchQueryComponent::alterQuery($request)))
            ],

        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
