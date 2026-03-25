<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Enums\StatusCode;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\TablePriceMaster\TablePriceMasterRequest;
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
        session()->forget('admin.table_price_master.list');
        session()->push('admin.table_price_master.list', url()->full());
        return Inertia::render('Admin/TablePriceMaster/Index', $this->mergeSession([
            'data' => [
                'title' => 'Danh sách giá bàn',
                'tablePrices' => $tablePrices->items(),
                'sortLinks' => $this->sortLinks('admin.table_price_master.index', [
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
        return Inertia::render('Admin/TablePriceMaster/Form', $this->mergeSession([
            'data' => [
                'title' => 'Thêm mới loại bàn',
                'urlBack' => session()->get('admin.table_price_master.list')[0] ?? route('admin.table_price_master.index'),
            ]
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TablePriceMasterRequest $request)
    {
        //
        if (! $this->tablePrices->create($request)) {
            $this->setFlash(__('Thêm thất bại'), 'error');
            return redirect()->route('admin.table_price_master.create');
        }
        $this->setFlash(__('Thêm thành công'), 'success');
        return redirect()->route('admin.table_price_master.index');
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
        return Inertia::render('Admin/TablePriceMaster/Form', $this->mergeSession([
            'data' => [
                'title' => 'Cập nhật loại bàn',
                'urlBack' => session()->get('admin.table_price_master.list')[0] ?? route('admin.table_price_master.index'),
                'isEdit' => true,
                'tablePrice' => $this->tablePrices->getById($id),
            ]
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TablePriceMasterRequest $request, string $id)
    {
        //
        if (! $this->tablePrices->update($id, $request)) {
            $this->setFlash(__('Cập nhật thất bại'), 'error');
            return redirect()->route('admin.table_price_master.edit', $id);
        }
        $this->setFlash(__('Cập nhật thành công'), 'success');
        return redirect()->route('admin.table_price_master.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        if (! $this->tablePrices->delete($id)) {
            return response()->json([
                'message' => 'Xóa thất bại',
            ], StatusCode::INTERNAL_ERR);
        }
        return response()->json([
            'message' => 'Xóa thành công',
        ], StatusCode::OK);
    }
}
