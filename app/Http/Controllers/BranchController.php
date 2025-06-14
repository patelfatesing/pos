<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\CashBreakdown;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // if (!auth()->user()->hasPermission('View')) {
        //     abort(403, 'Unauthorized - You do not have the required permission.');
        // }

        $data = Branch::where('is_deleted', 'no')->get();
        return view('branch.index', compact('data'));
    }

    public function getAvailableNotes()
    {

        $start_date = date('Y-m-d'); // your start date (set manually)
        $end_date = date('Y-m-d') . ' 23:59:59'; // today's date till end of day
        $noteCount = [];
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

        // Decode cash JSON to array
        $cashBreakdowns = CashBreakdown::select("denominations")->where('user_id', auth()->id())
            ->where('branch_id', $branch_id)
            // ->where('type', '!=', 'cashinhand')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->get();


        foreach ($cashBreakdowns as $breakdown) {
            $denominations1 = json_decode($breakdown->denominations, true);
            if (is_array($denominations1)) {
                foreach ($denominations1 as $denomination => $notes) {
                    foreach ($notes as $noteValue => $action) {
                        // Check for 'in' (added notes) and 'out' (removed notes)
                        if (isset($action['in'])) {
                            if (!isset($noteCount[$noteValue])) {
                                $noteCount[$noteValue] = 0;
                            }
                            $noteCount[$noteValue] += $action['in'];
                        }
                        if (isset($action['out'])) {
                            if (!isset($noteCount[$noteValue])) {
                                $noteCount[$noteValue] = 0;
                            }
                            $noteCount[$noteValue] -= $action['out'];
                        }
                    }
                }
            }
        }
        return response()->json($noteCount);
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

        $query = new Branch();

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('address', 'like', '%' . $searchValue . '%');
            });
        }

        $query->where('is_deleted', 'no');

        $recordsTotal = Branch::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        $url = url('/');
        foreach ($data as $store) {

            $action = '<div class="d-flex align-items-center list-action">';
            if ($store->is_warehouser != 'yes') {
                // $action .= '<a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="Delete"
                // href="#" onclick="delete_store(' . $store->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>';
            }

            $action .= '<a class="badge badge-primary mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="#" onclick="add_store_holiday(' . $store->id . ')"><i class="ri-calendar-event-line"></i></a>';
            $action .= '<a class="badge badge-primary mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="#" onclick="low_level_stock(' . $store->id . ')"><i class="ri-battery-low-line"></i></a>';
            $action .= '<a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="Edit"
                    href="' . url('/store/edit/' . $store->id) . '"><i class="ri-pencil-line mr-0"></i></a>';
            $action .= '</div>';

            $records[] = [
                'name' => $store->name,
                'address' => $store->address,
                'main_branch' => $store->main_branch,
                'is_active' => $store->is_active == 'yes'
                    ? '<span onclick=\'branchStatusChange("' . $store->id . '", "no")\'><div class="badge badge-success" style="cursor:pointer">Active</div></span>'
                    : '<span onclick=\'branchStatusChange("' . $store->id . '", "yes")\'><div class="badge badge-danger" style="cursor:pointer">Inactive</div></span>',
                'created_at' => date('d-m-Y h:s', strtotime($store->created_at)),
                'updated_at' => date('d-m-Y h:s', strtotime($store->updated_at)),
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
        // if (!auth()->user()->hasPermission('Insert')) {
        //     abort(403, 'Unauthorized - You do not have the required permission.');
        // }
        $validated = $request->validate([
            'name' => 'required|string|unique:branches,name',
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
        // if (!auth()->user()->hasPermission('Update')) {
        //     abort(403, 'Unauthorized - You do not have the required permission.');
        // }
        $record = Branch::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('branch.edit', compact('record'));
    }

    // Update a record
    public function update(Request $request)
    {
        $id = $request->id;

        // if (!auth()->user()->hasPermission('Update')) {
        //     abort(403, 'Unauthorized - You do not have the required permission.');
        // }
        $record = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:branches,name,' . $id,
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'in:yes,no',
        ]);

        $record->update($validated);

        return redirect()->route('branch.list')->with('success', 'Record updated successfully.');
    }

    // Soft delete a record
    public function destroy(Request $request)
    {
        $id = $request->id;
        // if (!auth()->user()->hasPermission('Delete')) {
        //     abort(403, 'Unauthorized - You do not have the required permission.');
        // }
        $record = Branch::findOrFail($id);
        $record->update(['is_deleted' => 'yes']);
        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.',
        ]);

        // return redirect()->route('branch.list')->with('success', 'Record deleted successfully.');
    }

    public function statusChange(Request $request)
    {
        $user = Branch::findOrFail($request->id);
        $user->is_active = $request->status;
        $user->save();

        return response()->json(['message' => 'Status updated successfully']);
    }
}
