<?php

namespace App\Repositories\TablePriceMaster;

use App\Components\CommonComponent;
use App\Http\Requests\Admin\TablePriceMaster\TablePriceMasterRequest;
use App\Models\TablePriceMaster;
use Illuminate\Http\Request;
use Nette\Utils\Paginator;

class TablePriceMasterRepository implements TablePriceMasterInterface
{
    private TablePriceMaster $tablePriceMaster;
    public function __construct(TablePriceMaster $tablePriceMaster)
    {
        $this->tablePriceMaster = $tablePriceMaster;
    }
    public function get(Request $request)
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->tablePriceMaster;
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder = $builder->where(function ($q) use ($request) {
                $q->orWhere(CommonComponent::escapeLikeSentence('price_name', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('price_per_hour', $request['free_word']));
            });
        }
        $tablePriceMasters = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($tablePriceMasters)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $tablePriceMasters = $builder->paginate($newSizeLimit);
        }
        return $tablePriceMasters;
    }

    public function getById($id) {}

    public function create(TablePriceMasterRequest $request) {}

    public function update($id, TablePriceMasterRequest $request) {}

    public function delete($id) {}
}
