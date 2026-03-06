<?php

namespace App\Repositories\Tables;

use App\Http\Requests\Admin\Table\TableRequest;
use App\Models\Table;

interface TableInterface
{
    public function get($request);
    public function getTableSession($request);

    public function getById($id);

    public function create(TableRequest $request);

    public function update($id, TableRequest $request);

    public function delete($id);
}
