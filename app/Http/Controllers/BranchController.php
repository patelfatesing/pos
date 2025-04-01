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
        if (!auth()->user()->hasPermission('View')) {
            abort(403, 'Unauthorized - You do not have the required permission.');
        }
        $data = Branch::where('is_deleted', 'no')->get();
        return view('branch.index', compact('data'));
    }

    public function getData(Request $request)
    {

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input('columns' . $orderColumnIndex . 'data', 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $query = Branch::query();

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('address', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = Branch::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        $url = url('/');
        foreach ($data as $employee) {

            $action = "";
            $action .= "<a href='" . $url . "/store/edit/" . $employee->id . "' class='btn btn-info mr_2'>Edit</a>";
            $action .= '<button type="button" onclick="delete_store(' . $employee->id . ')" class="btn btn-danger ml-2">Delete</button>';

            $records[] = [
                'name' => $employee->name,
                'address' => $employee->address,
                'is_active' => $employee->is_active,
                'created_at' => date('d-m-Y h:s', strtotime($employee->created_at)),
                'action' => $action
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
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
        if (!auth()->user()->hasPermission('Insert')) {
            abort(403, 'Unauthorized - You do not have the required permission.');
        }
        $validated = $request->validate([
            'name' => 'required|string|unique:branch,name',
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

        return redirect()->route('branch.list')->with('success', 'Record created successfully.');
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
        if (!auth()->user()->hasPermission('Update')) {
            abort(403, 'Unauthorized - You do not have the required permission.');
        }
        $record = Branch::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('branch.edit', compact('record'));
    }

    // Update a record
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('Update')) {
            abort(403, 'Unauthorized - You do not have the required permission.');
        }
        $record = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:branch,name,' . $id,
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'in:yes,no',
        ]);

        $record->update($validated);

        return redirect()->route('branch.list')->with('success', 'Record updated successfully.');
    }

    // Soft delete a record
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('Delete')) {
            abort(403, 'Unauthorized - You do not have the required permission.');
        }
        $record = Branch::findOrFail($id);
        $record->update(['is_deleted' => 'yes']);

        return redirect()->route('branch.list')->with('success', 'Record deleted successfully.');
    }

}
