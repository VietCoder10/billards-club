<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Http\Controllers\BaseController;
use App\Repositories\Invoice\InvoiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends BaseController
{
    private InvoiceInterface $invoice;
    public function __construct(InvoiceInterface $invoice)
    {
        $this->invoice = $invoice;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $invoices = $this->invoice->get($request);
        session()->forget('admin.invoice.list');
        session()->push('admin.invoice.list', url()->full());
        return Inertia::render('Admin/Invoice/Index', $this->mergeSession([
            'data' => [
                'title' => 'Danh sách hóa đơn',
                'invoices' => $invoices->items(),
                'sortLinks' => $this->sortLinks('admin.invoice.index', [
                    ['key' => 'invoice_number', 'name' => 'Số hóa đơn'],
                    ['key' => 'created_user_name', 'name' => 'Người tạo'],
                    ['key' => 'table_total', 'name' => 'Tiền bàn'],
                    ['key' => 'service_total', 'name' => 'tiền dịch vụ'],
                    ['key' => 'total_amount', 'name' => 'Tổng tiền'],
                    ['key' => 'final_amount', 'name' => 'Thành tiền'],
                    ['key' => 'payment_method', 'name' => 'Phương thức thanh toán']
                ], $request),
                'request' => $request->all(),
                'paginator' => $this->paginator($invoices->appends(SearchQueryComponent::alterQuery($request)))
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
        $invoice = $this->invoice->getById($id);
        $urlBack = session()->get('admin.invoice.list')[0] ?? route('admin.invoice.index');

        return Inertia::render('Admin/Invoice/Form', $this->mergeSession([
            'data' => [
                'title' => 'Chi tiết hóa đơn',
                'invoice' => $invoice,
                'invoiceDetails' => $invoice->invoice_details,
                'urlBack' => $urlBack
            ]
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
