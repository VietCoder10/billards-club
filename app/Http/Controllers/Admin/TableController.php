<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Http\Controllers\BaseController;
use App\Repositories\Tables\TableInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\TableStatus;
use App\Repositories\Option\OptionInterface;
use App\Http\Requests\Admin\Table\TableRequest;
use App\Enums\StatusCode;

class TableController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    private TableInterface $table;
    private OptionInterface $option;
    public function __construct(TableInterface $table, OptionInterface $option)
    {
        $this->table = $table;
        $this->option = $option;
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
        return Inertia::render('Admin/Table/Form', $this->mergeSession([
            'data' => [
                'title' => 'Thêm mới bàn',
                'tableStatus' => TableStatus::getOptions(),
                'tablePrices' => $this->option->getTablePrice(),
                'urlBack' => session()->get('admin.table.list')[0] ?? route('admin.table.index'),
            ]
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TableRequest $request)
    {
        //
        if (! $this->table->create($request)) {
            $this->setFlash(__('Thêm thất bại'), 'error');
            return redirect()->route('admin.table.create');
        }
        $this->setFlash(__('Thêm thành công'), 'success');
        return redirect()->route('admin.table.index');
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
        return Inertia::render('Admin/Table/Form', $this->mergeSession([
            'data' => [
                'title' => 'Cập nhật bàn',
                'urlBack' => session()->get('admin.table.list')[0] ?? route('admin.table.index'),
                'tableStatus' => TableStatus::getOptions(),
                'tablePrices' => $this->option->getTablePrice(),
                'table' => $this->table->getById($id),
                'isEdit' => true
            ]
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TableRequest $request, string $id)
    {
        //
        if (! $this->table->update($id, $request)) {
            $this->setFlash(__('Cập nhật thất bại'), 'error');
            return redirect()->route('admin.table.edit', $id);
        }
        $this->setFlash(__('Cập nhật thành công'), 'success');
        return redirect()->route('admin.table.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        if (! $this->table->delete($id)) {
            return response()->json([
                'message' => 'Xóa thất bại',
            ], StatusCode::INTERNAL_ERR);
        }
        return response()->json([
            'message' => 'Xóa thành công',
        ], StatusCode::OK);
    }
}
