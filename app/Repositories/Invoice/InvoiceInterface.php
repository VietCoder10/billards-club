<?php

namespace App\Repositories\Invoice;

use App\Http\Requests\Admin\Invoice\InvoiceRequest;
use Illuminate\Http\Request;

interface InvoiceInterface
{
    public function get($request);
    public function getById($id);
    public function create(InvoiceRequest $request);
    public function update(InvoiceRequest $request, $id);
    public function delete($id);
}
