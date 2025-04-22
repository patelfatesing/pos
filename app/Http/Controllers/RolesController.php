<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use Illuminate\Http\Request;

class RolesController extends Controller
{
      /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Roles::where('is_deleted', 'no')->get();
        return view('roles.index', compact('data'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id'); // Fixed input key
        $orderDirection = $request->input('order.0.dir', 'asc');

        // Validate order direction
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'asc';
        }

        $query = Roles::query()->where('is_deleted', 'no')->where('name', '!=', "owner");

        if (!empty($searchValue)) {
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        $recordsTotal = Roles::where('is_deleted', 'no')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];
        $url = url('/');

        foreach ($data as $role) {
            $action = "<a href='" . $url . "/roles/edit/" . $role->id . "' class='btn btn-info mr-2'>Edit</a>";
            $action .= '<button type="button" onclick="delete_role(' . $role->id . ')" class="btn btn-danger ml-2">Delete</button>';

            $records[] = [
                'name' => $role->name,
                'is_active' => $status = ($role->is_active ? '<div class="badge badge-success">Active</div>':'<div class="badge badge-success">Inactive</div>'),
                'created_at' => \Carbon\Carbon::parse($role->created_at)->format('d-m-Y H:i'),
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
           return view('roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'is_active' => 'nullable|in:yes,no',
        ], [
            'name.required' => 'The role name is required.',
            'name.unique' => 'This role name already exists.',
            'name.max' => 'The role name must not exceed 255 characters.',
            'is_active.in' => 'The is_active field must be either "yes" or "no".',
        ]);

        Roles::create([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? 'yes'
        ]);

        return redirect()->route('roles.list')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Roles $Roles)
    {
        //
    }

    // Show edit form
    public function edit($id)
    {
        $record = Roles::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('roles.edit', compact('record'));
    }

    // Update a record
    public function update(Request $request)
    {
        $id = $request->id;
        
        $record = Roles::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $id . '|max:255',
            'is_active' => 'nullable|in:yes,no',
        ], [
            'name.unique' => 'This role name already exists.',
            'name.max' => 'The role name must not exceed 255 characters.',
            'is_active.in' => 'The is_active field must be either "yes" or "no".',
        ]);

        $record->update($validated);

        return redirect()->route('roles.list')->with('success', 'Record updated successfully.');
    }

    // Soft delete a record
    public function destroy($id)
    {
        $record = Roles::where('id', $id)->where('is_deleted', 'no')->firstOrFail();

        $record->update(['is_deleted' => 'yes']);

        return redirect()->route('roles.list')->with('success', 'Record deleted successfully.');
    }
}
