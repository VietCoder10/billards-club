<?php

namespace App\Repositories\TablePriceMaster;

use App\Http\Requests\Admin\TablePriceMaster\TablePriceMasterRequest;
use Illuminate\Http\Request;

interface TablePriceMasterInterface
{
    public function get(Request $request);

    public function getById($id);

    public function create(TablePriceMasterRequest $request);

    public function update($id, TablePriceMasterRequest $request);

    public function delete($id);
}
