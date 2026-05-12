<?php

namespace App\Repositories\Invoice;

use App\Components\CommonComponent;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Http\Requests\Admin\Invoice\InvoiceRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class InvoiceRepository implements InvoiceInterface
{
    private Invoice $invoice;
    private Order $order;
    private Table $table;
    public function __construct(Invoice $invoice, Order $order, Table $table)
    {
        $this->invoice = $invoice;
        $this->order = $order;
        $this->table = $table;
    }
    public function get($request)
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->invoice
            ->leftJoin('users as user_updated', function ($q) {
                $q->on('invoices.updated_by', 'user_updated.id');
                $q->whereNull('user_updated.deleted_at');
            })->leftJoin('users as user_created', function ($q) {
                $q->on('invoices.created_by', 'user_created.id');
                $q->whereNull('user_created.deleted_at');
            })
            ->select(
                'user_created.name as created_user_name',
                'user_updated.name as updated_user_name ',
                'invoices.*'
            );
        $invoices = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($invoices)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $invoices = $builder->paginate($newSizeLimit);
        }
        return $invoices;
    }
    public function getById($id)
    {
        $invoice = $this->invoice->with('invoice_details')->find($id);
        return $invoice;
    }
    public function create(InvoiceRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $invoice = new Invoice;
            $invoice->fill([
                'invoice_number' => $request->order_number,
                'customer_id' => $request->customer_id,
                'table_name' => $request->table_name,
                'table_total' => $request->table_total,
                'service_total' => $request->service_total,
                'total_amount' => $request->final_total,
                'discount' => $request->discount ?? 0,
                'final_amount' => $request->final_total,
                'payment_method' => $request->payment_method,
                'paid_at' => Carbon::now(),
            ]);
            if (!$invoice->save()) {
                return false;
            }
            $order = $this->order->find($request->order_id);
            if (!$order) {
                return false;
            }
            $order->status = OrderStatus::COMPLETED;
            $order->total_minutes = $request->total_minutes;
            $order->table_total = $request->table_total;
            $order->service_total = $request->service_total;
            $order->final_total = $request->final_total;
            $order->ended_at = Carbon::now();
            if (!$order->save()) {
                return false;
            }
            $table = $this->table->find($order->table_id);
            if (!$table) {
                return false;
            }
            $table->status = TableStatus::AVAILABLE;
            if (!$table->save()) {
                return false;
            }
            $row = [];
            foreach ($request->details as $value) {
                $row[] = [
                    'invoice_id' => $invoice->id,
                    'item_name' => $value['item_name'] ?? ($value['product_name'] ?? ''),
                    'quantity' => $value['quantity'] ?? 0,
                    'price' => $value['price'] ?? 0,
                    'sub_total' => $value['sub_total'] ?? 0
                ];
            }
            if ($row) {
                $invoice->invoice_details()->createMany($row);
            }

            return true;
        });
    }
    public function update(InvoiceRequest $request, $id) {}
    public function delete($id) {}
}
