<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Repositories\Option\OptionInterface;
use App\Repositories\Products\ProductInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderItemController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    private ProductInterface $product;
    private OptionInterface $option;
    private \App\Repositories\Order\OrderInterface $order;
    public function __construct(ProductInterface $product, \App\Repositories\Order\OrderInterface $order, OptionInterface $option)
    {
        $this->product = $product;
        $this->order = $order;
        $this->option = $option;
    }
    public function index(Request $request)
    {
        //
        return Inertia::render('Admin/Order/OrderItem', $this->mergeSession([
            'data' => [
                'title' => 'Chi tiết đơn hàng',
                'products' => $this->product->get($request)
            ]
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
        return Inertia::render('Admin/Order/OrderItem', $this->mergeSession([
            'data' => [
                'title' => 'Chi tiết đơn hàng',
                'order' => $this->order->getById($id),
                'products' => $this->product->get(request()->merge(['limit_page' => 100])),
                'categories' => \App\Enums\ProductCategory::getOptions(),
                'userOptions' => $this->option->getUser(),
                'urlBack' => session()->get('admin.order.list')[0] ?? route('admin.order.index'),
            ]
        ]));
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
