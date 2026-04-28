<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\CashBreakdown;
use Illuminate\Support\Carbon;
use App\Models\Accounting\AccountLedger;

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
            // echo "<pre>";
            // print_r($denominations1);
            if (is_array($denominations1)) {
                // Handle array of objects: [{"10":{"in":"0"}},{"20":{"in":"0"}},...]
                if (array_keys($denominations1) === range(0, count($denominations1) - 1)) {
                    foreach ($denominations1 as $item) {
                        if (is_array($item)) {
                            foreach ($item as $noteValue => $action) {
                                if (isset($action['in'])) {
                                    if (!isset($noteCount[$noteValue])) {
                                        $noteCount[$noteValue] = 0;
                                    }
                                    $noteCount[$noteValue] += (int)$action['in'];
                                }
                                if (isset($action['out'])) {
                                    if (!isset($noteCount[$noteValue])) {
                                        $noteCount[$noteValue] = 0;
                                    }
                                    $noteCount[$noteValue] -= (int)$action['out'];
                                }
                            }
                        }
                    }
                } else {
                    // Handle object with nested arrays: {"5":{"500":{"in":4}}, "3":{"100":{"out":1}}}
                    foreach ($denominations1 as $outer) {
                        if (is_array($outer)) {
                            foreach ($outer as $noteValue => $action) {
                                if (isset($action['in'])) {
                                    if (!isset($noteCount[$noteValue])) {
                                        $noteCount[$noteValue] = 0;
                                    }
                                    $noteCount[$noteValue] += (int)$action['in'];
                                }
                                if (isset($action['out'])) {
                                    if (!isset($noteCount[$noteValue])) {
                                        $noteCount[$noteValue] = 0;
                                    }
                                    $noteCount[$noteValue] -= (int)$action['out'];
                                }
                            }
                        }
                    }
                }
            }
        }
        return response()->json($noteCount);
    }

    public function getGrandTotalCash()
    {
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d') . ' 23:59:59';

        $branch_id = (!empty(auth()->user()->userinfo->branch->id))
            ? auth()->user()->userinfo->branch->id
            : "";

        $total = CashBreakdown::where('user_id', auth()->id())
            ->where('branch_id', $branch_id)
            ->whereBetween('created_at', [$start_date, $end_date])
            ->sum('total'); // 👈 direct sum

        return response()->json([
            'grand_total' => $total
        ]);
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

        $query = Branch::leftJoin('account_ledgers', 'branches.bank_ledger_id', '=', 'account_ledgers.id')
            ->select('branches.*', 'account_ledgers.name as bank_ledger_name');

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('branches.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('branches.address', 'like', '%' . $searchValue . '%')
                    ->orWhere('account_ledgers.name', 'like', '%' . $searchValue . '%');
            });
        }

        $query->where('branches.is_deleted', 'no');

        $recordsTotal = Branch::count();
        $recordsFiltered = $query->count();
        $roleId = auth()->user()->role_id;

        $userId = auth()->id();

        $listAccess = getAccess($roleId, 'store-manage');

        // ❌ No permission → return empty table
        if (in_array($listAccess, ['none', 'no'])) {
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        // 👤 Own permission → only own products
        if ($listAccess === 'own') {
            $query->where('created_by', $userId);
        }

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        $url = url('/');
        foreach ($data as $store) {
            $ownerId = $store->created_by;
            // if (canDo($roleId, 'product-edit', $ownerId)) {
            // }
            $action = '<div class="d-flex align-items-center list-action">';
            if ($store->is_warehouser != 'yes') {
                // $action .= '<a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="Delete"
                // href="#" onclick="delete_store(' . $store->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>';
            }
            if (canDo($roleId, 'add-holiday', $ownerId)) {
                $action .= '<a class="badge badge-primary mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="#" onclick="add_store_holiday(' . $store->id . ')"><i class="ri-calendar-event-line"></i></a>';
            }
            if (canDo($roleId, 'one-time-sales', $ownerId)) {
                $action .= '<a class="badge badge-primary mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="#" onclick="add_one_time_sales(' . $store->id . ')"><i class="ri-price-tag-line"></i></a>';
            }
            if (canDo($roleId, 'product-low-stock-set', $ownerId)) {
                $action .= '<a class="badge badge-primary mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="#" onclick="low_level_stock(' . $store->id . ')"><i class="ri-battery-low-line"></i></a>';
            }
            if (canDo($roleId, 'store-edit', $ownerId)) {
                $action .= '<a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="Edit"
                    href="' . url('/store/edit/' . $store->id) . '"><i class="ri-pencil-line mr-0"></i></a>';

                $action .= '<div class="custom-control custom-switch custom-control-inline">
                        <input type="checkbox" class="custom-control-input" id="customSwitch' . $store->id . '" ' . ($store->in_out_enable ? 'checked' : '') . ' data-store-id="' . $store->id . '">
                        <label class="custom-control-label" for="customSwitch' . $store->id . '">
                            <span class="switch-label">' . ($store->in_out_enable ? 'In/Out' : 'In/Out') . '</span>
                        </label>
                    </div>';

                $action .= '<div class="custom-control custom-switch custom-control-inline">
                        <input type="checkbox" class="custom-control-input capture-status-switch" id="captureSwitch' . $store->id . '" ' . ($store->is_capture ? 'checked' : '') . ' data-store-id="' . $store->id . '">
                        <label class="custom-control-label" for="captureSwitch' . $store->id . '">
                            <span class="switch-label">' . ($store->is_capture ? 'Capture' : 'Capture') . '</span>
                        </label>
                    </div>';
            }

            $action .= '</div>';

            $records[] = [
                'name' => $store->name,
                'address' => $store->address,
                'main_branch' => $store->main_branch,
                'bank_ledger' => $store->bank_ledger_name ?? '-',
                'is_active' => $store->is_active == 'yes'
                    ? '<span onclick=\'branchStatusChange("' . $store->id . '", "no")\'><div class="badge badge-success" style="cursor:pointer">Active</div></span>'
                    : '<span onclick=\'branchStatusChange("' . $store->id . '", "yes")\'><div class="badge badge-danger" style="cursor:pointer">Inactive</div></span>',
                'created_at' => $store->created_at->format('d-m-Y h:i A'),
                'updated_at' => $store->updated_at->format('d-m-Y h:i A'),
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
        $acc_ledger = AccountLedger::where('group_id', 17)->pluck('name', 'id');
        return view('branch.create', compact('acc_ledger'));
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
            'bank_ledger_id' => 'required'
        ]);

        Branch::create([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? 'yes',
            'is_deleted' => 'no',
            'created_by' => auth()->id(),
            'bank_ledger_id' => $validated['bank_ledger_id'],
        ]);

        return redirect()->route('branch.list')->with('success', 'Record created successfully.');
    }

    public function updateStatus(Request $request)
    {
        // Validate the request
        $request->validate([
            'store_id' => 'required|exists:branches,id', // Ensure the store exists
            'in_out_enable' => 'required|boolean', // Validate is_active as boolean
        ]);

        // Find the store by ID
        $store = Branch::findOrFail($request->store_id);

        // Update the store's active status
        $store->in_out_enable = $request->in_out_enable;
        $store->updated_by = auth()->id();
        $store->save(); // Save the updated store

        // Return a response indicating success
        return response()->json([
            'message' => 'Store status updated successfully.',
            'status' => $store->in_out_enable ? 'enable' : 'disable'
        ]);
    }

    public function updateCaptureStatus(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:branches,id',
            'is_capture' => 'required|boolean',
        ]);

        $store = Branch::findOrFail($request->store_id);
        $store->is_capture = $request->is_capture;
        $store->updated_by = auth()->id();
        $store->save();

        return response()->json([
            'message' => 'Capture status updated successfully.',
            'status' => $store->is_capture ? 'enable' : 'disable'
        ]);
    }

    // Show edit form
    public function edit($id)
    {
        // if (!auth()->user()->hasPermission('Update')) {
        //     abort(403, 'Unauthorized - You do not have the required permission.');
        // }

        $acc_ledger = AccountLedger::where('group_id', 17)->pluck('name', 'id');

        $record = Branch::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('branch.edit', compact('record', 'acc_ledger'));
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
            'bank_ledger_id' => 'required'
        ]);

        // add updated_by
        $validated['updated_by'] = auth()->id(); // or Auth::id()

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

    public function addOneTimeStore(Request $request)
    {
        $validated = $request->validate([
            'branch_id'      => ['required', 'exists:branches,id'],
            'one_time_sales' => ['required', 'date_format:H:i'], // only time format allowed
        ]);

        // Enforce minimum 19:00
        if ($validated['one_time_sales'] < "19:00") {
            return response()->json([
                'message' => 'Time must be 19:00 (7:00 PM) or later.'
            ], 422);
        }

        $branch = Branch::findOrFail($validated['branch_id']);
        $branch->one_time_sales = $validated['one_time_sales']; // saves only TIME
        $branch->save();

        return response()->json([
            'message' => 'One time sales saved.',
            'time'    => $branch->one_time_sales,
        ]);
    }
}
