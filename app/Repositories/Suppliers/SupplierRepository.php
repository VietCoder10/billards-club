<?php   
namespace App\Repositories\Suppliers;

use App\Components\CommonComponent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\Supplier\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierRepository implements SupplierInterface {
    private Supplier $supplier;
    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }
    public function get(Request $request): LengthAwarePaginator{
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->supplier;
        if(isset($request['free_word']) && $request['free_word'] != ''){
            $builder = $builder->where(function($query) use ($request){
                $query->orWhere('supplier_name','LIKE',"%{$request['free_word']}%");
            });

        }
        $suppliers = $builder->sortable(['updated_at'=>'desc'])->paginate($newSizeLimit);
        return $suppliers;
    }
    public function getById(int $id): ?Supplier
    {
        return $this->supplier->where('id', $id)->first();
    }
    public function store(SupplierRequest $request): bool
    {
        $supplier = new Supplier();
        $supplier->supplier_name = $request->supplier_name;
        $supplier->contact_person = $request->contact_person;
        $supplier->email = $request->email;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        $supplier->note = $request->note;
        $supplier->status = $request->status;
        return $supplier->save();

    }
    public function update(SupplierRequest $request, string $id): bool
    {
        $supplier = $this->getById($id);
        if(! $supplier){
            return false;
        }
        $supplier->supplier_name = $request->supplier_name;
        $supplier->contact_person = $request->contact_person;
        $supplier->email = $request->email;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        $supplier->note = $request->note;
        $supplier->status = $request->status;
        return $supplier->save();

    }
    public function destroy(int $id)
    {
        $supplier = $this->getById($id);
        if(! $supplier){
            return false;
        }
        return $supplier->delete();
    }
}