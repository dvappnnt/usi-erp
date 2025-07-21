<?php

namespace App\Http\Controllers\Modules\RequisitionManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequisitionVoucher;
use Inertia\Inertia;

class RequisitionVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = RequisitionVoucher::with('items')->get(); // Load related items
        return Inertia::render('Modules/RequisitionVoucher/Index', ['vouchers' => $vouchers]);
    }

    public function create()
    {
        return Inertia::render('Modules/RequisitionVoucher/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'purpose' => 'required|string',
            'date' => 'required|date',
            'requisitioner' => 'required|string|max:255',
            'requisitioner_position' => 'required|string|max:255',
            'recommending_approval' => 'required|string|max:255',
            'recommending_position' => 'required|string|max:255',
            'approved_by' => 'required|string|max:255',
            'approved_position' => 'required|string|max:255',

            // Validate items array
            'items' => 'required|array|min:1',
            'items.*.mat_code' => 'nullable|string|max:255',
            'items.*.particular' => 'required|string|max:255',
            'items.*.quantity' => 'required|string|max:255',
        ]);

        // Save Requisition Voucher
        $voucher = RequisitionVoucher::create([
            'purpose' => $validated['purpose'],
            'date' => $validated['date'],
            'requisitioner' => $validated['requisitioner'],
            'requisitioner_position' => $validated['requisitioner_position'],
            'recommending_approval' => $validated['recommending_approval'],
            'recommending_position' => $validated['recommending_position'],
            'approved_by' => $validated['approved_by'],
            'approved_position' => $validated['approved_position'],
        ]);

        // Save each item
        foreach ($validated['items'] as $item) {
            $voucher->items()->create($item);
        }

        return redirect()->route('requisition-vouchers.index')
            ->with('toast', 'Requisition Voucher created successfully!');
    }

    public function edit($id)
    {
        $voucher = RequisitionVoucher::with('items')->findOrFail($id);
        return inertia('Modules/RequisitionVoucher/Edit', [
            'voucher' => $voucher
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'purpose' => 'required|string',
            'date' => 'required|date',
            'requisitioner' => 'required|string|max:255',
            'requisitioner_position' => 'required|string|max:255',
            'recommending_approval' => 'required|string|max:255',
            'recommending_position' => 'required|string|max:255',
            'approved_by' => 'required|string|max:255',
            'approved_position' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.mat_code' => 'nullable|string',
            'items.*.particular' => 'required|string|max:255',
            'items.*.quantity' => 'required|string|max:255',
        ]);

        $voucher = RequisitionVoucher::findOrFail($id);
        $voucher->update([
            'purpose' => $validated['purpose'],
            'date' => $validated['date'],
            'requisitioner' => $validated['requisitioner'],
            'requisitioner_position' => $validated['requisitioner_position'],
            'recommending_approval' => $validated['recommending_approval'],
            'recommending_position' => $validated['recommending_position'],
            'approved_by' => $validated['approved_by'],
            'approved_position' => $validated['approved_position'],
        ]);

        // Sync items: delete old, add new
        $voucher->items()->delete();

        foreach ($validated['items'] as $item) {
            $voucher->items()->create($item);
        }

        return redirect()->route('requisition-vouchers.index')->with('toast', 'Requisition Voucher updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        RequisitionVoucher::destroy($id);

        return redirect()->route('requisition-vouchers.index')->with('toast', 'Requisition Voucher deleted successfully!');
    }
}
