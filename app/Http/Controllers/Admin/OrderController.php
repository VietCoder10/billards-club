<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\Order\OrderRequest;
use App\Models\Order;
use App\Models\Table;
use App\Repositories\Option\OptionInterface;
use App\Repositories\Order\OrderInterface;
use App\Repositories\Tables\TableInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\In;
use Inertia\Inertia;
use League\Uri\Idna\Option;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    private OptionInterface $option;
    private TableInterface $table;
    private OrderInterface $order;
    public function __construct(TableInterface $table, OrderInterface $order, OptionInterface $option)
    {
        $this->table = $table;
        $this->order = $order;
        $this->option = $option;
    }
    public function index(Request $request)
    {
        $orders = $this->order->get($request);
        session()->forget('admin.order.list');
        session()->push('admin.order.list', url()->full());
        return Inertia::render('Admin/Order/Index', $this->mergeSession([
            'data' => [
                'title' => 'Danh sách đơn hàng',
                'orders' => $orders->items(),
                'sortLinks' => $this->sortLinks('admin.order.index', [
                    ['key' => 'table_name', 'name' => 'Tên bàn'],
                    ['key' => 'started_at', 'name' => 'Ngày tạo'],
                    ['key' => 'ended_at', 'name' => 'Ngày kết thúc'],
                    ['key' => 'table_total', 'name' => 'Tổng tiền bàn'],
                    ['key' => 'service_total', 'name' => 'Tổng tiền dịch vụ'],
                    ['key' => 'final_total', 'name' => 'Tổng tiền đơn hàng'],
                    ['key' => 'status', 'name' => 'Trạng thái'],
                ], $request),
                'request' => $request->all(),
                'paginator' => $this->paginator($orders->appends(SearchQueryComponent::alterQuery($request)))
            ],
        ]));
    }

    public function indexSession(Request $request)
    {
        //
        $table = $this->table->getTableSession($request);
        session()->forget('admin.order.list');
        session()->push('admin.order.list', url()->full());
        return Inertia::render('Admin/Order/Order', $this->mergeSession([
            'data' => [
                'title' => 'Danh sách đơn hàng',
                'tables' => $table,
                'request' => $request->all(),
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
    public function store(OrderRequest $request)
    {
        $order = $this->order->create($request);
        return redirect()->route('admin.order-item.edit', $order->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        return Inertia::render('Admin/Order/Form', $this->mergeSession([
            'data' => [
                'title' => 'Chỉnh sửa đơn hàng',
                'order' => $this->order->getById($id),
                'productOptions' => $this->option->getProduct(),
                'urlBack' => session()->get('admin.order.list')[0] ?? route('admin.order.index'),

            ],
        ]));
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
    public function update(\App\Http\Requests\Admin\Order\OrderRequest $request, string $id)
    {
        $result = $this->order->update($request, $id);
        if ($result) {
            return redirect()->back()->with('success', 'Cập nhật đơn hàng thành công');
        }
        return redirect()->back()->with('error', 'Cập nhật đơn hàng thất bại');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
