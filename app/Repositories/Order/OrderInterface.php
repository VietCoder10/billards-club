<?php

namespace App\Repositories\Order;

use App\Http\Requests\Admin\Order\OrderRequest;

interface OrderInterface
{
    public function get($request);
    public function getById($id);
    public function create(OrderRequest $request);
    public function update(OrderRequest $request, $id);
    public function delete($id);
}
