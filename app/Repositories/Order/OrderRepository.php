<?php

namespace App\Repositories\Order;

use App\Components\CommonComponent;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Http\Requests\Admin\Order\OrderRequest;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Paginator;
use Illuminate\Validation\ValidationException;

class OrderRepository implements OrderInterface
{
    private Order $orders;
    private Product $product;
    public function __construct(Order $orders, Product $product)
    {
        $this->orders = $orders;
        $this->product = $product;
    }
    public function get($request)
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->orders
            ->leftJoin('tables', function ($q) {
                $q->on('orders.table_id', '=', 'tables.id');
                $q->whereNull('tables.deleted_at');
            })
            ->leftJoin('order_details', function ($q) {
                $q->on('orders.id', '=', 'order_details.order_id');
                $q->whereNull('order_details.deleted_at');
            })->select('orders.*', 'tables.table_name')->groupBy('orders.id', 'tables.table_name');
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder = $builder->where(function ($q) use ($request) {
                $q->orWhere(CommonComponent::escapeLikeSentence('table_name', $request['free_word']));
            });
        }
        $orders = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($orders)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $orders = $builder->paginate($newSizeLimit);
        }
        return $orders;
    }
    public function getById($id)
    {
        return $this->orders
            ->with(['details', 'table'])
            ->where('id', $id)
            ->first();
    }
    public function create(OrderRequest $request)
    {
        $order = new Order();
        $order->table_id = $request->table_id;
        $order->user_id = auth()->id();
        $order->status = OrderStatus::PENDING;
        $order->started_at = now();
        $order->order_number = 'ORD-' . strtoupper(\Illuminate\Support\Str::random(8));

        $table = Table::find($request->table_id);
        if ($table) {
            $order->price_per_hour = $table->tablePrice->price_per_hour ?? 0;
            $table->status = 2;
            $table->save();
        }

        $order->save();
        return $order;
    }
    public function update(OrderRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $order = $this->orders->with('details')->findOrFail($id);

            // Return current quantities to stock to recalculate correctly
            foreach ($order->details as $detail) {
                if ($detail->product_id) {
                    $product = $this->product->find($detail->product_id);
                    if ($product) {
                        $product->quantity += $detail->quantity;
                        $product->save();
                    }
                }
            }

            $order = $this->fillOrderData($request, $order);
            $order->save();

            $itemIds = collect($request->order_details)->pluck('id')->filter()->toArray();
            $order->details()->whereNotIn('id', $itemIds)->delete();

            foreach ($request->order_details ?? [] as $value) {
                if ($value['product_id'] != null) {
                    $product = $this->product->find($value['product_id']);
                    if (!$product) {
                        throw ValidationException::withMessages([
                            'order_details' => ["Sản phẩm không tồn tại."]
                        ]);
                    }

                    if ($product->quantity < $value['quantity']) {
                        throw ValidationException::withMessages([
                            'order_details' => ["Sản phẩm {$product->product_name} hết hàng hoặc không đủ số lượng. Tồn kho: {$product->quantity}"]
                        ]);
                    }

                    $product->quantity -= $value['quantity'];
                    $product->update();
                }

                $row = [
                    'order_id' => $order->id,
                    'product_id' => $value['product_id'],
                    'product_name' => $value['product_name'],
                    'quantity' => $value['quantity'],
                    'price' => $value['price'],
                    'sub_total' => $value['sub_total']
                ];

                $order_details = OrderDetail::where([
                    ['id', $value['id'] ?? ''],
                    ['order_id', $order->id]
                ])->first();

                if (!$order_details) {
                    $order_details = new OrderDetail;
                    $order_details->fill($row);
                    $order_details->save();
                } else {
                    $order_details->fill($row);
                    $order_details->save();
                }
            }
            return true;
        });
    }
    public function delete($id)
    {
        return $this->orders->destroy($id);
    }
    private function fillOrderData(OrderRequest $request, Order $order): Order
    {
        $order->order_number = $request->order_number;
        $order->table_id = $request->table_id;
        $order->note = $request->note;
        $order->status = $request->status; //
        $order->ended_at = $request->status
            ? ($order->status == OrderStatus::PENDING
                ? now()
                : ($request->ended_at ? Carbon::parse($request->ended_at) : now()))
            : null;
        $order->price_per_hour = $request->price_per_hour;
        $order->total_minutes = $request->total_minutes ?? 0;
        $order->table_total = $request->table_total ?? 0;
        $order->service_total = $request->service_total ?? 0;
        $order->final_total = $request->final_total ?? 0;
        $order->note = $request->note;
        return $order;
    }
}
