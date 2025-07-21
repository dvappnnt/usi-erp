<?php

namespace App\Http\Controllers;


use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Signatory;
use App\Models\Supplier;
use App\Models\SupplierProductDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $requests = PurchaseRequest::with('items.product')->latest()->paginate(10);
        $signatories = Signatory::where('module', 'purchase-request')->get();

        return Inertia::render('Modules/PurchaseRequests/Index', [
            'requests' => $requests,
            'signatories' => $signatories,
        ]);
    }

    public function create()

    {
        $suppliers = Supplier::with('supplierProductDetails.product')->get();

        return Inertia::render('Modules/PurchaseRequests/Create', [
            'suppliers' => $suppliers
        ]);
    }
    public function generatePRNumber()
    {
        $date = now()->format('Ymd');
        $count = PurchaseRequest::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return 'PR-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'purpose' => 'nullable',
            'date' => 'required|date',
            'prepared_by' => 'nullable',
            'approved_by' => 'nullable',
            'received_by' => 'nullable',
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.item_no' => 'nullable|integer',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer',
            'items.*.units' => 'required|string',
        ]);

        // Auto-generate PR Number
        $prNumber = $this->generatePRNumber();

        // Create Purchase Request with generated PR number
        $purchaseRequest = PurchaseRequest::create([
            'pr_no' => $prNumber,
            'purpose' => $validated['purpose'] ?? null,
            'date' => $validated['date'],
            'supplier_id' => $validated['supplier_id'],
        ]);

        // Create items
        foreach ($validated['items'] as $item) {
            $purchaseRequest->items()->create($item);
        }

        return redirect()->route('purchase-requests.index')->with('success', 'Purchase Request created successfully.');
    }


    public function show(PurchaseRequest $purchaseRequest)
    {
        return inertia('Modules/PurchaseRequests/Show', [
            'purchaseRequest' => $purchaseRequest->load('items'),
        ]);
    }

    public function edit($id)
    {
        $request = PurchaseRequest::with('items')->findOrFail($id);

        // Fetch suppliers with their products for dropdown
        $suppliers = Supplier::with('supplierProductDetails.product')->get();

        return inertia('Modules/PurchaseRequests/Edit', [
            'request' => $request,
            'suppliers' => $suppliers
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'pr_no' => 'required|unique:purchase_requests,pr_no,' . $id,
            'purpose' => 'nullable',
            'date' => 'required|date',
            // 'prepared_by' => 'nullable',
            // 'approved_by' => 'nullable',
            // 'received_by' => 'nullable',
            'items' => 'required|array|min:1',
            'items.*.item_no' => 'nullable|integer',
            'items.*.product_id' => 'required|exists:products,id', // Correct product_id
            'items.*.qty' => 'required|integer',
            'items.*.units' => 'required|string',
        ]);

        $purchaseRequest = PurchaseRequest::findOrFail($id);

        // Update Purchase Request main data
        $purchaseRequest->update([
            'pr_no' => $validated['pr_no'],
            'purpose' => $validated['purpose'],
            'date' => $validated['date'],
            'prepared_by' => $validated['prepared_by'],
            'approved_by' => $validated['approved_by'],
            'received_by' => $validated['received_by'],
        ]);

        // Remove old items
        $purchaseRequest->items()->delete();

        // Insert updated items
        foreach ($validated['items'] as $item) {
            $purchaseRequest->items()->create($item);
        }

        return redirect()->route('purchase-requests.index')->with('success', 'Purchase Request updated successfully.');
    }

    public function destroy($id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')->with('success', 'Purchase Request deleted successfully.');
    }
}
