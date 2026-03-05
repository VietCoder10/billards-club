<?php

namespace App\Repositories\Order;

use App\Components\CommonComponent;
use App\Http\Requests\Admin\Order\OrderRequest;
use App\Models\Order;
use Nette\Utils\Paginator;

class OrderRepository implements OrderInterface
{
    private Order $orders;
    public function __construct(Order $orders)
    {
        $this->orders = $orders;
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
            })->select('orders.*', 'tables.table_name');
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
    public function getById($id) {}
    public function create(OrderRequest $request) {}
    public function update(OrderRequest $request, $id) {}
    public function delete($id) {}
}
