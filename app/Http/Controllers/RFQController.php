<?php

namespace App\Http\Controllers;

use App\Mail\RFQMail;
use App\Models\RFQ;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class RFQController extends Controller
{
    public function index()
    {
        $rfqs = RFQ::with(['pr', 'supplier', 'items'])->latest()->paginate(10);
        return Inertia::render('Modules/RFQ/Index', ['rfqs' => $rfqs]);
    }

    public function create()
    {
        return Inertia::render('Modules/RFQ/Create', [
            'prs' => PurchaseRequest::select('id', 'pr_no')->get(),
            'suppliers' => Supplier::with('supplierProductDetails.product')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_id' => 'required|exists:purchase_requests,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'date_sent' => 'required|date',
            'deadline' => 'required|date|after_or_equal:date_sent',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|string',
        ]);

        $rfq = RFQ::create([
            ...$validated,
            'rfq_no' => $this->generateRFQNumber(),
        ]);

        $rfq->items()->createMany($validated['items']);

        return redirect()->route('rfqs.index')->with('success', 'RFQ created successfully.');
    }

    public function edit(RFQ $rfq)
    {
        $rfq->load(['items', 'pr', 'supplier']);

        return Inertia::render('Modules/RFQ/Edit', [
            'rfq' => $rfq,
            'prs' => PurchaseRequest::select('id', 'pr_no')->get(),
            'suppliers' => Supplier::with('supplierProductDetails.product')->select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, RFQ $rfq)
    {
        $validated = $request->validate([
            'pr_id' => 'required|exists:purchase_requests,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'date_sent' => 'required|date',
            'deadline' => 'required|date|after_or_equal:date_sent',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|string',
        ]);

        $rfq->update([
            'pr_id' => $validated['pr_id'],
            'supplier_id' => $validated['supplier_id'],
            'date_sent' => $validated['date_sent'],
            'deadline' => $validated['deadline'],
        ]);

        $rfq->items()->delete();
        $rfq->items()->createMany($validated['items']);

        return redirect()->route('rfqs.index')->with('success', 'RFQ updated successfully.');
    }

    public function fetchPR($id)
    {
        $pr = PurchaseRequest::with(['items.product', 'supplier'])->findOrFail($id);

        return response()->json([
            'supplier' => $pr->supplier,
            'items' => $pr->items,
        ]);
    }

    public function fetchSupplierProducts($supplierId)
    {
        $supplier = Supplier::with('supplierProductDetails.product')->findOrFail($supplierId);
        $products = $supplier->supplierProductDetails->pluck('product')->filter()->values();

        return response()->json(['products' => $products]);
    }

    private function generateRFQNumber(): string
    {
        $date = now()->format('Ymd');
        $count = RFQ::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return 'RFQ-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }


    public function destroy(RFQ $rfq)
    {

        $rfq->delete();

        return redirect()->route('rfqs.index')->with('success', 'RFQ deleted successfully.');
    }
    public function sendEmail($id)
{
    $rfq = RFQ::with(['pr', 'supplier', 'items'])->findOrFail($id);

    if (!$rfq->supplier || !$rfq->supplier->email) {
        return back()->withErrors(['email' => 'No supplier email found.']);
    }
     if ($rfq->status === 'sent') {
        return back()->withErrors(['email' => 'Request for Quotation already sent to supplier.']);
    }

    Mail::to($rfq->supplier->email)->send(new RFQMail($rfq));
    $rfq->status = 'sent';
    $rfq->save();

    return back()->with('success', 'RFQ email sent successfully.');
}
}
