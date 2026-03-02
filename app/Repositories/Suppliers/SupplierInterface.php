<?php

namespace App\Repositories\Suppliers;

use App\Http\Requests\Admin\Supplier\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;


interface SupplierInterface {
    public function get(Request $request): LengthAwarePaginator;
    public function getById(int $id): ?Supplier;
    public function store(SupplierRequest $request):bool;
    public function update(SupplierRequest $request, string $id):bool;
    public function destroy(int $id);

}