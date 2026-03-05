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
        $builder = $this->tables;
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder = $builder->where(function ($q) use ($request) {
                $q->orWhere(CommonComponent::escapeLikeSentence('table_number', $request['free_word']));
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
