<?php

namespace App\Repositories\Products;

use App\Components\CommonComponent;
use App\Enums\StorageFolder;
use App\Http\Requests\Admin\Product\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class ProductRepository implements ProductInterface
{
    private Product $products;
    public function __construct(Product $products)
    {
        $this->products = $products;
    }
    public function get(Request $request)
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->products
            ->join('suppliers', function ($q) {
                $q->on('products.supplier_id', '=', 'suppliers.id');
                $q->whereNull('suppliers.deleted_at');
            })
            ->select('products.*', 'suppliers.supplier_name');
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $builder = $builder->where(function ($q) use ($request) {
                $q->orWhere(CommonComponent::escapeLikeSentence('product_name', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('category', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('sku', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('supplier_name', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('cost_price', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('sale_price', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('total_amount', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('quantity', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('description', $request['free_word']));
            });
        }
        $products = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($products)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $products = $builder->paginate($newSizeLimit);
        }

        return $products;
    }
    public function getById($id)
    {
        return $this->products->find($id);
    }
    public function create(ProductRequest $request)
    {
        $products = new Product();
        $products->fill($request->all());

        if ($request->hasFile('avatar')) {
            $filename = CommonComponent::uploadFileName($request->avatar->getClientOriginalExtension());
            $folder = StorageFolder::PRODUCT;
            $path = CommonComponent::uploadFile($folder, $request->avatar, $filename);
            if (!$path) {
                return false;
            }
            $products->avatar = $path;
        }

        if (!$products->save()) {
            return false;
        }
        return true;
    }
    public function update(ProductRequest $request, $id)
    {
        $product = $this->getById($id);
        if (!$product) {
            return false;
        }
        $product->fill($request->all());

        if ($request->hasFile('avatar')) {
            if ($product->avatar) {
                CommonComponent::deleteFile('', $product->avatar);
            }
            $filename = CommonComponent::uploadFileName($request->avatar->getClientOriginalExtension());
            $folder = StorageFolder::PRODUCT;
            $path = CommonComponent::uploadFile($folder, $request->avatar, $filename);
            if (!$path) {
                return false;
            }
            $product->avatar = $path;
        } elseif ($request->has('avatar') && $request->avatar === null) {
            if ($product->avatar) {
                CommonComponent::deleteFile('', $product->avatar);
                $product->avatar = null;
            }
        }

        if (!$product->save()) {
            return false;
        }
        return true;
    }
    public function delete($id)
    {
        $product = $this->getById($id);
        if (!$product) {
            return false;
        }
        if (!$product->delete()) {
            return false;
        }
        return true;
    }
}
