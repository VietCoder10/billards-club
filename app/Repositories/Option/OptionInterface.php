<?php

namespace App\Repositories\Option;

interface OptionInterface
{
    public function getSupplier();
    public function getProduct();
    public function getUser();
    public function getTablePrice();
    public function getCustomer();
}
