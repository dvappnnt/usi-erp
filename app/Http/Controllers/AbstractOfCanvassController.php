<?php

namespace App\Http\Controllers;

use App\Models\AbstractOfCanvass;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierProductDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbstractOfCanvassController extends Controller
{
    //
    // public function index()
    // {
    //     return Inertia::render('Modules/AOC/Index', [
    //         'aocs' => AbstractOfCanvass::with('purchaseRequest')->latest()->paginate(10),
    //     ]);
    // }
//     public function index()
// {
//     return Inertia::render('Modules/AOC/Index', [
//         'aocs' => AbstractOfCanvass::with([
//             'purchaseRequest',        // for PR number
//             'items.product',          // for item.product.name
//             'items.supplier',         // for item.supplier.name
//         ])->latest()->paginate(10),
//     ]);
// }
 public function index()
    {
        $aocs = AbstractOfCanvass::with([
            'purchaseRequest',
            'items.product',
            'items.supplier',
        ])->latest()->paginate(10);

        $aocs->getCollection()->transform(function ($aoc) {
            $suppliers = $aoc->items->pluck('supplier')->unique('id')->values();

            $productMatrix = [];

            foreach ($aoc->items->groupBy('product_id') as $productId => $groupedItems) {
                $first = $groupedItems->first();
                $row = [
                    'qty' => $first->qty,
                    'unit' => $first->units,
                    'product' => $first->product->name ?? '—',
                    'suppliers' => [],
                ];

                foreach ($groupedItems as $item) {
                    $row['suppliers'][$item->supplier_id] = [
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ];
                }

                $productMatrix[] = $row;
            }

            $aoc->unique_suppliers = $suppliers;
            $aoc->product_matrix = $productMatrix;

            return $aoc;
        });

        return Inertia::render('Modules/AOC/Index', [
            'aocs' => $aocs,
        ]);
    }


    public function create()
    {
        return Inertia::render('Modules/AOC/Create', [
            'purchaseRequests' => PurchaseRequest::with('items')->get(),
            'suppliers' => Supplier::all(),
            'products' => Product::all(),
            'supplierProductDetails' => SupplierProductDetail::select('product_id', 'supplier_id', 'price')->get(),
        ]);
    }

    public function store(Request $request)
    {
        // Generate Reference Number
        $today = Carbon::today()->format('Ymd');
        $latest = AbstractOfCanvass::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $latest ? ((int)substr($latest->reference_no, -3)) + 1 : 1;
        $referenceNo = 'AOC-' . $today . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Create AOC
        $aoc = AbstractOfCanvass::create([
            'purchase_request_id' => $request->purchase_request_id,
            'reference_no' => $referenceNo,
        ]);

        foreach ($request->items as $item) {
            $aoc->items()->create([
                'product_id' => $item['product_id'],
                'supplier_id' => $item['supplier_id'],
                'qty' => $item['qty'],
                'units' => $item['units'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
            ]);
        }

        return redirect()->route('aocs.index')->with('success', 'AOC created');
    }
    public function edit($id)
    {
        $aoc = AbstractOfCanvass::with('items')->findOrFail($id);

        return Inertia::render('Modules/AOC/Edit', [
            'aoc' => $aoc,
            'purchaseRequests' => PurchaseRequest::with('items')->get(),
            'suppliers' => Supplier::all(),
            'products' => Product::all(),
            'supplierProductDetails' => SupplierProductDetail::select('product_id', 'supplier_id', 'price')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $aoc = AbstractOfCanvass::findOrFail($id);
        $aoc->update([
            'reference_no' => $request->reference_no,
        ]);

        $aoc->items()->delete();
        foreach ($request->items as $item) {
            $aoc->items()->create($item);
        }

        return redirect()->route('aocs.index')->with('success', 'AOC updated');
    }
}
