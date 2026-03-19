<?php

namespace App\Repositories\Option;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

class OptionRepository implements OptionInterface
{
    public function getSupplier()
    {
        return Supplier::select('id as value', 'supplier_name as label')->get();
    }
    public function getProduct()
    {
        return Product::select('id as value', 'product_name as label', 'sale_price')->get();
    }
    public function getUser()
    {
        return User::select('id as value', 'name as label','user_role')->get();
    }
}
