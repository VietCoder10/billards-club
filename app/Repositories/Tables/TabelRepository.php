<?php

namespace App\Repositories\Tables;

use App\Components\CommonComponent;
use App\Http\Requests\Admin\Table\TableRequest;
use App\Models\Table;
use Illuminate\Pagination\Paginator;

class TabelRepository implements TableInterface
{
    private Table $tables;
    public function __construct(Table $tables)
    {
        $this->tables = $tables;
    }
    public function get($request)
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->tables
            ->leftJoin('table_price_master', 'tables.table_price_id', '=', 'table_price_master.id')
            ->leftJoin('orders', function ($join) {
                $join->on('tables.id', '=', 'orders.table_id')
                    ->where('orders.status', '=', \App\Enums\OrderStatus::PENDING);
            })
            ->select('tables.*', 'table_price_master.price_per_hour', 'orders.id as order_id');
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder = $builder->where(function ($q) use ($request) {
                $q->orWhere(CommonComponent::escapeLikeSentence('table_name', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('status', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('price_per_hour', $request['free_word']));
            });
        }
        $tables = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($tables)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $tables = $builder->paginate($newSizeLimit);
        }
        return $tables;
    }

    public function getById($id) {}

    public function create(TableRequest $request) {}

    public function update($id, TableRequest $request) {}

    public function delete($id) {}
}
