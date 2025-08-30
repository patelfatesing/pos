<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseLedger;
use App\Models\VendorList;
use Illuminate\Support\Str;

class PurchaseLedgerController extends Controller
{
    public function index()
    {
        $purchaseLedger = PurchaseLedger::latest()->paginate(10);
        $vendorList = VendorList::get();
        return view('purchase-ledgers.index', compact('purchaseLedger', 'vendorList'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'asc';
        }

        $query = PurchaseLedger::with('vendor');

        if (!empty($searchValue)) {
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        $recordsTotal = PurchaseLedger::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];
        foreach ($data as $role) {
            $action = '<div class="d-flex align-items-center list-action">
                        <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="Edit"
                            href="' . url('/purchase-ledger/edit/' . $role->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                    </div>';

            $records[] = [
                'name' => $role->name,
                'expense_type' => $role->vendor->name ?? '<span class="badge bg-secondary">N/A</span>',
                'is_active' => ($role->is_active == 'Yes'
                    ? '<div class="badge badge-success" onclick=\'statusChange("' . $role->id . '", "No")\'>Active</div>'
                    : '<div class="badge badge-danger" onclick=\'statusChange("' . $role->id . '", "Yes")\'>Inactive</div>'),
                'created_at' => $role->created_at ? $role->created_at->format('d-m-Y H:i') : '',
                'updated_at' => $role->updated_at ? $role->updated_at->format('d-m-Y H:i') : '',
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

    public function create()
    {
        return view('purchase-ledgers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:expense_categories,name'
        ]);

        PurchaseLedger::create([
            'name' => $request->name,
            'vendor_id' => $request->vendor_id
        ]);

        return response()->json(['message' => 'Purchase Ledger created successfully.']);
    }

    public function show(PurchaseLedger $expenseCategory)
    {
        return view('purchase-ledgers.show', compact('expenseCategory'));
    }

    public function edit($id)
    {
        $record = PurchaseLedger::where('id', $id)->firstOrFail();
         $vendorList = VendorList::get();

        return view('purchase-ledgers.edit', compact('record','vendorList'));
    }

    public function update(Request $request)
    {

        $id = $request->id;

        $expenseCategory = PurchaseLedger::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:expense_categories,name,' . $id,
        ]);

        $expenseCategory->update([
            'name' => $request->name,
            'vendor_id' => $request->vendor_id,
           
        ]);

        return redirect()->route('purchase_ledger.list')
            ->with('success', 'Purchase Ledger updated successfully.');
    }

    public function destroy(PurchaseLedger $expenseCategory)
    {
        $expenseCategory->delete();

        return redirect()->route('exp_category.list')
            ->with('success', 'Purchase Ledger deleted successfully.');
    }

    public function statusChange(Request $request)
    {
        $user = PurchaseLedger::findOrFail($request->id);
        $user->is_active = $request->status;
        $user->save();

        return response()->json(['message' => 'Status updated successfully']);
    }
}
