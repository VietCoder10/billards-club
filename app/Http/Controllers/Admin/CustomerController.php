<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Enums\StatusCode;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\Customer\StoreCustomerRequest;
use App\Http\Requests\Admin\Product\UpdateAvatarRequest;
use App\Repositories\Customer\CustomerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CustomerController extends BaseController
{
    private CustomerInterface $interface;

    public function __construct(CustomerInterface $interface)
    {
        $this->interface = $interface;
    }

    public function searchModalCustomer(Request $request)
    {
        $customers = $this->interface->getModalCustomer($request);
        return response()->json([
            'data' => $customers->items(),
            'total' => $customers->total(),
            'current_page' => $customers->currentPage(),
            'per_page' => $customers->perPage(),
            'last_page' => $customers->lastPage(),
        ], StatusCode::OK);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response
    {
        $customers = $this->interface->get($request);
        session()->forget('admin.customer.list');
        session()->push('admin.customer.list', url()->full());

        return Inertia::render('Admin/Customer/Index', $this->mergeSession([
            'data' => [
                'title' => 'Quản lí khách hàng',
                'customers' => $customers->items(),
                'sortLinks' => $this->sortLinks('admin.customer.index', [
                    ['key' => 'name', 'name' => 'Tên khách hàng'],
                    ['key' => 'email', 'name' => 'Email'],
                    ['key' => 'phone', 'name' => 'Số điện thoại'],
                ], $request),
                'request' => $request->all(),
                'paginator' => $this->paginator($customers->appends(SearchQueryComponent::alterQuery($request))),
            ],
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Customer/Form', [
            'data' => [
                'title' => 'Thêm khách hàng',
                'urlBack' => session()->get('admin.customer.list')[0] ?? route('admin.customer.index'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        if ($this->interface->store($request)) {
            $this->setFlash(__('Thêm khách hàng thành công.'), 'success');

            return redirect()->route('admin.customer.index');
        }
        $this->setFlash(__('Thêm khách hàng thất bại.'), 'error');

        return redirect()->route('admin.customer.create');
    }

    public function storeModalCustomer(Request $request)
    {
        $customer = $this->interface->storeModalCustomer($request);
        if (!$customer) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __(' khách hàng đã tồn tại!'),
                ], StatusCode::INTERNAL_ERR);
            }
            $this->setFlash(__(' khách hàng đã tồn tại!'), 'error');
            return back()->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Thêm mới khách hàng thành công!'),
                'customer' => $customer,
            ], StatusCode::OK);
        }

        $this->setFlash(__('Thêm mới khách hàng thành công!'), 'success');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = $this->interface->getById($id);
        if (!$customer) {
            $this->setFlash(__('Không tìm thấy khách hàng.'), 'error');

            return redirect()->route('admin.customer.index');
        }

        return Inertia::render('Admin/Customer/Form', [
            'data' => [
                'title' => 'Cập nhật khách hàng',
                'customer' => $customer,
                'isEdit' => true,
                'urlBack' => session()->get('admin.customer.list')[0] ?? route('admin.customer.index'),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCustomerRequest $request, string $id)
    {
        if ($this->interface->update($request, $id)) {
            $this->setFlash(__('Cập nhật thông tin thành công.'));

            return redirect()->route('admin.customer.index');
        }
        $this->setFlash(__('Cập nhật thông tin thất bại.'), 'error');

        return redirect()->route('admin.customer.edit', $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ($this->interface->destroy($id)) {
            return response()->json([
                'message' => 'Xóa khách hàng thành công.',
            ], StatusCode::OK);
        }

        return response()->json([
            'message' => 'Xóa khách hàng thất bại.',
        ], StatusCode::INTERNAL_ERR);
    }

    public function checkEmail(Request $request)
    {
        return response()->json([
            'valid' => $this->interface->checkEmail($request),
        ], StatusCode::OK);
    }

    public function updateAvatar(UpdateAvatarRequest $request, $id)
    {
        $customer = $this->interface->getById($id);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy khách hàng.',
            ], StatusCode::NOT_FOUND);
        }

        try {
            // Delete old avatar if exists
            if ($customer->avatar && Storage::disk('public')->exists($customer->avatar)) {
                Storage::disk('public')->delete($customer->avatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $customer->avatar = $path;
            $customer->save();

            return response()->json([
                'success' => true,
                'avatar' => Storage::url($path),
                'message' => 'Ảnh đại diện đã được cập nhật.',
            ], StatusCode::OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi.',
            ], StatusCode::INTERNAL_ERR);
        }
    }
}
