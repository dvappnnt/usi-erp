<?php

namespace App\Http\Controllers\Modules\WarehouseManagement;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    protected $modelClass;
    protected $modelName;
    protected $modulePath;

    public function __construct()
    {
        $this->modelClass = \App\Models\PurchaseOrder::class;
        $this->modelName = Str::plural(Str::singular(class_basename($this->modelClass)));
        $this->modulePath = 'Modules/WarehouseManagement';
    }

    public function index()
    {
        return Inertia::render("{$this->modulePath}/{$this->modelName}/Index");
    }

    public function create()
    {
        return Inertia::render("{$this->modulePath}/{$this->modelName}/Create");
    }
    public function show($id)
    {
        $model = $this->modelClass::with([
            'company',
            'warehouse',
            'purchaseRequests.items.product',
            'supplier.purchaseRequests' => function ($query) {
                // Only fetch PRs not yet linked to a PO
                $query->whereDoesntHave('purchaseOrders');
            },
            'supplier.purchaseRequests.items.product',
        ])->findOrFail($id);

        return Inertia::render("{$this->modulePath}/{$this->modelName}/Show", [
            'modelData' => $model,
        ]);
    }
    public function attachPurchaseRequest(Request $request, $purchaseOrderId)
    {
        $request->validate([
            'purchase_request_id' => 'required|exists:purchase_requests,id',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);

        // Attach the PR
        $purchaseOrder->purchaseRequests()->attach($request->purchase_request_id);

        return response()->json(['message' => 'Purchase Request attached successfully.']);
    }
    public function detachPurchaseRequest(Request $request, $purchaseOrderId)
    {
        $request->validate([
            'purchase_request_id' => 'required|exists:purchase_requests,id',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);

        // Detach the PR
        $purchaseOrder->purchaseRequests()->detach($request->purchase_request_id);

        return response()->json(['message' => 'Purchase Request detached successfully.']);
    }



    public function edit($id)
    {
        $model = $this->modelClass::findOrFail($id);

        return Inertia::render("{$this->modulePath}/{$this->modelName}/Edit", [
            'modelData' => $model,
        ]);
    }

    public function export()
    {
        return Inertia::render("{$this->modulePath}/{$this->modelName}/Export");
    }

    public function print($id)
    {
        $purchaseOrder = $this->modelClass::with([
            'company',
            'supplier',
            'warehouse',
            'details.supplierProductDetail.product',
            'details.supplierProductDetail.variation',
        ])->findOrFail($id);

        return Inertia::render("{$this->modulePath}/{$this->modelName}/Print", [
            'modelData' => $purchaseOrder,
            'purchaseOrderDetails' => $purchaseOrder->details,
        ]);
    }
}
