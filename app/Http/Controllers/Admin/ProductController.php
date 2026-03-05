<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Http\Controllers\BaseController;
use App\Models\Product;
use App\Repositories\Products\ProductInterface;
use Faker\Provider\Base;
use App\Http\Requests\Admin\Product\UpdateAvatarRequest;
use App\Enums\StatusCode;
use App\Http\Requests\Admin\Product\ProductRequest;
use App\Repositories\Option\OptionInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use League\Uri\Idna\Option;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    private ProductInterface $product;
    private OptionInterface $option;
    public function __construct(ProductInterface $productRepository, OptionInterface $optionRepository)
    {
        $this->product = $productRepository;
        $this->option = $optionRepository;
    }
    public function index(Request $request)
    {
        //
        $products = $this->product->get($request);
        session()->forget('admin.product.list');
        session()->push('admin.product.list', url()->full());
        return inertia('Admin/Product/Index', $this->mergeSession([
            'data' => [
                'title' => 'Danh sách sản phẩm',
                'products' => $products->items(),
                'sortLinks' => $this->sortLinks('admin.product.index', [
                    ['key' => 'product_name', 'name' => 'Tên sản phẩm'],
                    ['key' => 'category', 'name' => 'Danh mục'],
                    ['key' => 'sku', 'name' => 'SKU'],
                    ['key' => 'supplier_name', 'name' => 'Nhà cung cấp'],
                    ['key' => 'cost_price', 'name' => 'Giá nhập'],
                    ['key' => 'quantity', 'name' => 'Số lượng'],
                ], $request),
                'request' => $request->all(),
                'paginator' => $this->paginator($products->appends(SearchQueryComponent::alterQuery($request)))
            ]
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return inertia('Admin/Product/Form', $this->mergeSession([
            'data' => [
                'title' => 'Thêm sản phẩm',
                'supplierOptions' => $this->option->getSupplier(),
                'urlBack' => session()->get('admin.product.list')[0] ?? route('admin.product.index'),
            ]
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        //
        if (!$this->product->create($request)) {
            $this->setFlash(__('Tạo sản phẩm thất bại'), 'error');
            return redirect()->route('admin.product.create');
        }
        $this->setFlash(__('Tạo sản phẩm thành công'), 'success');
        return redirect()->route('admin.product.index');
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
        $product = $this->product->getById($id);
        $urlBack = session()->get('admin.product.list')[0] ?? route('admin.product.index');
        if (!$product) {
            return redirect()->route('admin.product.index')->with('error', 'Không tìm thấy sản phẩm.');
        }
        return inertia('Admin/Product/Form', $this->mergeSession([
            'data' => [
                'title' => 'Chi tiết sản phẩm',
                'isEdit' => true,
                'supplierOptions' => $this->option->getSupplier(),
                'product' => $product,
                'urlBack' => $urlBack,
            ]
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, string $id)
    {
        //
        if (!$this->product->update($request, $id)) {
            $this->setFlash(__('Cập nhật sản phẩm thất bại'), 'error');
            return redirect()->route('admin.product.edit', $id);
        }
        $this->setFlash(__('Cập nhật sản phẩm thành công'), 'success');
        return redirect()->route('admin.product.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Update the specified resource's avatar in storage.
     */
    public function updateAvatar(UpdateAvatarRequest $request, string $id)
    {
        $product = $this->product->getById($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.',
            ], StatusCode::NOT_FOUND);
        }

        try {
            // Delete old avatar if exists
            if ($product->avatar && Storage::disk('public')->exists($product->avatar)) {
                Storage::disk('public')->delete($product->avatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $product->avatar = $path;
            $product->save();

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
