<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Http\Controllers\BaseController;
use App\Models\Table;
use App\Repositories\TablePriceMaster\TablePriceMasterInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TablePriceMasterController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    private TablePriceMasterInterface $tablePrices;
    public function __construct(TablePriceMasterInterface $tablePrices)
    {
        $this->tablePrices = $tablePrices;
    }
    public function index(Request $request)
    {
        //
        $tablePrices = $this->tablePrices->get($request);
        session()->forget('admin.table-price-master.list');
        session()->push('admin.table-price-master.list', url()->full());
        return Inertia::render('Admin/TablePriceMaster/Index', $this->mergeSession([
            'data' => [
                'title' => 'Danh sách giá bàn',
                'tablePrices' => $tablePrices->items(),
                'sortLinks' => $this->sortLinks('admin.table-price-master.index', [
                    ['key' => 'price_name', 'name' => 'Tên loại bàn'],
                    ['key' => 'price_per_hour', 'name' => 'Giá tiền theo giờ'],
                ], request()),
                'request' => request()->all(),
                'paginator' => $this->paginator($tablePrices->appends(SearchQueryComponent::alterQuery($request))),
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
