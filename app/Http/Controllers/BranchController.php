<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Branch::where('is_deleted', 'no')->get();
        return view('branch.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
           return view('branch.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:my_models,name',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'in:yes,no',
        ]);

        Branch::create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? 'yes',
            'is_deleted' => 'no',
        ]);

        return redirect()->route('branch.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        //
    }

    // Show edit form
    public function edit($id)
    {
        $record = Branch::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('branch.edit', compact('record'));
    }

    // Update a record
    public function update(Request $request, $id)
    {
        $record = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:my_models,name,' . $id,
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'in:yes,no',
        ]);

        $record->update($validated);

        return redirect()->route('myrecords.index')->with('success', 'Record updated successfully.');
    }

    // Soft delete a record
    public function destroy($id)
    {
        $record = Branch::findOrFail($id);
        $record->update(['is_deleted' => 'yes']);

        return redirect()->route('branch.index')->with('success', 'Record deleted successfully.');
    }

}
