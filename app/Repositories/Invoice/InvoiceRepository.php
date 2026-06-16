<?php

namespace App\Repositories\Invoice;

use App\Components\CommonComponent;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Http\Requests\Admin\Invoice\InvoiceRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
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
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder->where(function ($query) use ($request) {
                $query->where(CommonComponent::escapeLikeSentence('invoice_number', $request['free_word']));
            });
        }
        $invoices = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($invoices)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $invoices = $builder->paginate($newSizeLimit);
        }
        return $invoices;
    }
    public function getFromCustomer($request, $customerId)
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->invoice
            ->where('customer_id', $customerId)
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
                'table_total' => round($request->table_total),
                'service_total' => round($request->service_total),
                'total_amount' => round($request->table_total + $request->service_total),
                'discount' => round($request->discount ?? 0),
                'final_amount' => round($request->final_total),
                'payment_method' => $request->payment_method,
                'paid_at' => Carbon::now(),
            ]);
            if (!$invoice->save()) {
                return false;
            }

            $order = $this->order->with('details')->find($request->order_id);
            if (!$order) {
                return false;
            }

            // Return old quantities to stock before updating details
            foreach ($order->details as $detail) {
                if ($detail->product_id) {
                    $product = Product::find($detail->product_id);
                    if ($product) {
                        $product->quantity += $detail->quantity;
                        $product->save();
                    }
                }
            }

            // Remove old details
            $order->details()->forceDelete();

            // Save new details to the database and adjust stock levels
            foreach ($request->details ?? [] as $value) {
                if (!empty($value['product_id'])) {
                    $product = Product::find($value['product_id']);
                    if (!$product) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'details' => ["Sản phẩm không tồn tại."]
                        ]);
                    }

                    if ($product->quantity < $value['quantity']) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'details' => ["Sản phẩm {$product->product_name} hết hàng hoặc không đủ số lượng. Tồn kho: {$product->quantity}"]
                        ]);
                    }

                    $product->quantity -= $value['quantity'];
                    $product->update();

                    $orderDetail = new OrderDetail;
                    $orderDetail->fill([
                        'order_id' => $order->id,
                        'product_id' => $value['product_id'],
                        'avatar' => $product->avatar,
                        'product_name' => $value['item_name'] ?? $value['product_name'] ?? '',
                        'quantity' => $value['quantity'],
                        'price' => round($value['price'] ?? 0),
                        'sub_total' => round($value['sub_total'] ?? 0)
                    ]);
                    $orderDetail->save();
                }
            }

            $order->status = OrderStatus::COMPLETED;
            $order->total_minutes = $request->total_minutes;
            $order->table_total = round($request->table_total);
            $order->service_total = round($request->service_total);
            $order->final_total = round($request->final_total);
            $order->ended_at = $request->ended_at ? Carbon::parse($request->ended_at) : Carbon::now();
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
                    'price' => round($value['price'] ?? 0),
                    'sub_total' => round($value['sub_total'] ?? 0)
                ];
            }
            if ($row) {
                $invoice->invoice_details()->createMany($row);
            }

            return $invoice;
        });
    }
    public function update(InvoiceRequest $request, $id) {}
    public function delete($id) {}
}
