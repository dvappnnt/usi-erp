<?php

namespace App\Http\Controllers;

use App\Models\Signatory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SignatoryController extends Controller
{
    public function index()
    {
        $signatories = Signatory::latest()->paginate(10);

        return Inertia::render('Modules/Signatories/Index', [
            'signatories' => $signatories
        ]);
    }

    public function create()
    {
        return Inertia::render('Modules/Signatories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'name' => 'required|string',
            'label' => 'required|string',
            'position' => 'required|string',
        ]);

        Signatory::create($validated);

        return redirect()->route('signatories.index')->with('success', 'Signatory created successfully.');
    }

    public function edit(Signatory $signatory)
    {
        return Inertia::render('Modules/Signatories/Edit', [
            'signatory' => $signatory
        ]);
    }

    public function update(Request $request, Signatory $signatory)
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'name' => 'required|string',
            'label' => 'required|string',
            'position' => 'required|string',
        ]);

        $signatory->update($validated);

        return redirect()->route('signatories.index')->with('success', 'Signatory updated successfully.');
    }

    public function destroy(Signatory $signatory)
    {
        $signatory->delete();

        return redirect()->route('signatories.index')->with('success', 'Signatory deleted successfully.');
    }
}
