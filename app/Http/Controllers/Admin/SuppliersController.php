<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Enums\Status;
use App\Enums\StatusCode;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\Supplier\SupplierRequest;
use App\Models\Supplier;
use App\Repositories\Suppliers\SupplierInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SuppliersController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    private $supplier;
    public function __construct(SupplierInterface $supplier)
    {
        $this->supplier = $supplier;
    }
    public function index(Request $request)
    {
        //
        $suppliers = $this->supplier->get($request);
        session()->forget('admin.supplier.list');
        session()->push('admin.supplier.list', url()->full());
        return Inertia::render('Admin/Supplier/Index', $this->mergeSession([
            'data' => [
                'title' => 'Danh sách nhà cung cấp',
                'suppliers' => $suppliers->items(),
                'sortLinks' => $this->sortLinks('admin.supplier.index', [
                    ['key' => 'supplier_name', 'name' => 'Tên'],
                    ['key' => 'contact_person', 'name' => 'Người liên hệ'],
                    ['key' => 'email', 'name' => 'Email'],
                    ['key' => 'phone', 'name' => 'Số điện thoại'],
                    ['key' => 'address', 'name' => 'Địa chỉ'],
                    ['key' => 'note', 'name' => 'Ghi chú'],
                    ['key' => 'status', 'name' => 'Trạng thái']
                ], $request),
                'request' => $request->all(),
                'paginator' => $this->paginator($suppliers->appends(SearchQueryComponent::alterQuery($request))),
            ]
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Admin/Supplier/Form', [
            'data' => [
                'title' => 'Thêm nhà cung cấp',
                'supplierStatus' => Status::getOptions(),
                'urlBack' => session()->get('admin.supplier.list')[0] ?? route('admin.supplier.index')
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        //
        $supplier = $this->supplier->store($request);
        if (! $supplier) {
            $this->setFlash('Thêm nhà cung cấp thất bại', 'error');
            return redirect()->route('admin.supplier.create');
        }

        $this->setFlash('Thêm nhà cung cấp thành công', 'success');
        return redirect()->route('admin.supplier.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $supplier = $this->supplier->getById($id);
        if (! $supplier) {
            $this->setFlash(__('Không tìm thấy nhà cung cấp'), 'error');

            return redirect()->route('admin.supplier.index');
        }
        return Inertia::render('Admin/Supplier/Form', [
            'data' => [
                'title' => 'Chỉnh sửa nhà cung cấp',
                'supplierStatus' => Status::getOptions(),
                'supplier' => $supplier,
                'isEdit' => true,
                'urlBack' => session()->get('admin.supplier.list')[0] ?? route('admin.supplier.index'),
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, string $id)
    {
        //
        $supplier = $this->supplier->update($request, $id);
        if (! $supplier) {
            $this->setFlash('Cập nhật nhà cung cấp thất bại', 'error');
            return redirect()->route('admin.supplier.edit', $id);
        }
        $this->setFlash(__('Cập nhật nhà cung cấp thành công'), 'success');
        return redirect()->route('admin.supplier.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $supplier = $this->supplier->destroy($id);
        if (! $supplier) {
            return response()->json([
                'message' => 'Xóa nhà cung cấp không thành công'
            ], StatusCode::INTERNAL_ERR);
        }
        return response()->json([
            'message' => 'Xóa nhà cung cấp thành công'
        ], StatusCode::OK);
    }
}
