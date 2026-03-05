<?php

namespace App\Repositories\Products;

use App\Http\Requests\Admin\Product\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Profiler\Profile;

interface ProductInterface
{
    public function get(Request $request);
    public function getById($id);
    public function create(ProductRequest $request);
    public function update(ProductRequest $request, $id);
    public function delete($id);
}
