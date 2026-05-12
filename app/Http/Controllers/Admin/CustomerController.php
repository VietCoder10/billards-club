<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Repositories\Customer\CustomerInterface;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    private $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    public function searchModal(Request $request)
    {
        $customers = $this->customer->searchModal($request);
        return response()->json($customers);
    }

    public function storeModel(Request $request)
    {
        $customer = $this->customer->storeModel($request);
        if ($customer) {
            return response()->json([
                'message' => 'Thêm khách hàng thành công',
                'data' => $customer
            ]);
        }
        return response()->json([
            'message' => 'Thêm khách hàng thất bại'
        ], 500);
    }
}
