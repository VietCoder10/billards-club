<?php

namespace App\Repositories\Option;

use App\Enums\Status;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\TablePrice;
use App\Models\TablePriceMaster;

class OptionRepository implements OptionInterface
{
    public function getSupplier()
    {
        return Supplier::select('id as value', 'supplier_name as label')
            ->where('status', Status::ACTIVE)->get();
    }
    public function getProduct()
    {
        return Product::select('id as value', 'product_name as label', 'sale_price')->get();
    }
    public function getUser()
    {
        return User::select('id as value', 'name as label', 'user_role')->get();
    }
    public function getTablePrice()
    {
        return TablePriceMaster::select('id as value', 'price_per_hour as label')->get();
    }
    public function getCustomer()
    {
        return Customer::select('id as value', 'name as label')->get();
    }
}
