<?php

namespace App\Repositories\Tables;

use App\Components\CommonComponent;
use App\Enums\OrderStatus;
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
                    ->where('orders.status', '=', OrderStatus::PENDING);
            })
            ->select('tables.*', 'table_price_master.price_per_hour', 'orders.id as order_id');
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder = $builder->where(function ($q) use ($request) {
                $q->orWhere(CommonComponent::escapeLikeSentence('table_name', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('status', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('price_per_hour', $request['free_word']));
            });
        }
        $tables = $builder->sortable(['id' => 'asc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($tables)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $tables = $builder->paginate($newSizeLimit);
        }
        return $tables;
    }
    public function getTableSession($request)
    {
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
        $tables = $builder->sortable(['id' => 'asc'])->get();
        return $tables;
    }


    public function getById(string $id)
    {
        return $this->tables->find($id);
    }

    public function create(TableRequest $request)
    {
        $table = $this->tables->fill($request->all());
        if (!$table->save()) {
            return false;
        }
        return true;
    }

    public function update(string $id, TableRequest $request)
    {
        $table = $this->getById($id);
        if (!$table) {
            return false;
        }
        $table->fill($request->all());
        if (!$table->save()) {
            return false;
        }
        return true;
    }

    public function delete(string $id)
    {
        $table = $this->tables->find($id);
        if (!$table->delete()) {
            return false;
        }
        return true;
    }
}
