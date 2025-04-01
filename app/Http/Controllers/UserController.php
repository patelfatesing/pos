<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
          /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = User::where('is_deleted', 'no')->get();
        return view('user.index', compact('data'));
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

        $query = User::query();

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = User::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        $url = url('/');
        foreach ($data as $employee) {

            $action = "";
            $action .= "<a href='" . $url . "/users/edit/" . $employee->id . "' class='btn btn-info mr_2'>Edit</a>";
            $action .= '<button type="button" onclick="delete_role(' . $employee->id . ')" class="btn btn-danger ml-2">Delete</button>';

            $records[] = [
                'name' => $employee->name,
                'role_id' => $employee->role_id,
                'work_branch_id' => $employee->work_branch_id,
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
           return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|unique:user,role_name',
            'is_active' => 'in:yes,no',
        ]);

        User::create([
            'role_name' => $validated['role_name'],
            'is_active' => $validated['is_active'] ?? 'yes'
        ]);

        return redirect()->route('user.list')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $User)
    {
        //
    }

    // Show edit form
    public function edit($id)
    {
        $record = User::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('user.edit', compact('record'));
    }

    // Update a record
    public function update(Request $request, $id)
    {
        $record = User::findOrFail($id);

        $validated = $request->validate([
            'role_name' => 'sometimes|string|unique:user,role_name,' . $id,
            'is_active' => 'in:yes,no',
        ]);

        $record->update($validated);

        return redirect()->route('user.list')->with('success', 'Record updated successfully.');
    }

    // Soft delete a record
    public function destroy($id)
    {
        $record = User::findOrFail($id);
        $record->update(['is_deleted' => 'yes']);

        return redirect()->route('user.list')->with('success', 'Record deleted successfully.');
    }
}
