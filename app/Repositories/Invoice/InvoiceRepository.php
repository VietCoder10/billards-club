<?php

namespace App\Repositories\Invoice;

use App\Components\CommonComponent;
use App\Http\Requests\Admin\Invoice\InvoiceRequest;
use App\Models\Invoice;
use Illuminate\Pagination\Paginator;

class InvoiceRepository implements InvoiceInterface
{
    private Invoice $invoice;
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
    public function get($request)
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $builder = $this->invoice
            ->leftJoin('users as user_updated', function ($q) {
                $q->on('invoices.updated_by', 'user_updated.id');
                $q->whereNull('user_updated.deleted_at');
            })->leftJoin('users as user_created', function ($q) {
                $q->on('invoices.created_by', 'user_created.id');
                $q->whereNull('user_created.deleted_at');
            })
            ->select(
                'user_created.name as created_user_name',
                'user_updated.name as updated_user_name ',
                'invoices.*'
            );
        $invoices = $builder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($invoices)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $invoices = $builder->paginate($newSizeLimit);
        }
        return $invoices;
    }
    public function getById($id)
    {
        $invoice = $this->invoice->with('invoice_details')->find($id);
        return $invoice;
    }
    public function create(InvoiceRequest $request) {}
    public function update(InvoiceRequest $request, $id) {}
    public function delete($id) {}
}
