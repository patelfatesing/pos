<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VendorList;
use App\Models\Purchase;
use App\Models\Accounting\AccountLedger;
use Illuminate\Support\Facades\Auth;

class VendorListController extends Controller
{
    public function index()
    {
        // sendNotification('low_stock', 'Item ABC is low on stock', 1, Auth::id());
        $VendorLists = VendorList::get();
        return view('vendors.index', compact('VendorLists'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input('columns.' . $orderColumnIndex . '.data', 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $query = new VendorList;

        // **Search filter**
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('gst_number', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = VendorList::count();
        $recordsFiltered = $query->count();

        $roleId = auth()->user()->role_id;

        $userId = auth()->id();

        $listAccess = getAccess($roleId, 'vendor-manage');

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

        foreach ($data as $VendorList) {
            // $ownerId = $product->created_by; 
            // if (canDo($roleId, 'vendor-edit', $ownerId)) {
            // }
            $action = '<div class="d-flex align-items-center list-action">
                    <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                    href="' . url('/vendor/edit/' . $VendorList->id) . '"><i class="ri-pencil-line mr-0"></i></a>';
            $action .= '   <a class="badge bg-danger mr-1" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#" onclick="delete_vendor(' . $VendorList->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>  </div>';


            $records[] = [
                'name' => $VendorList->name,
                'email' => $VendorList->email,
                'phone' => $VendorList->phone,
                'type' => $VendorList->type,
                'gst_number' => $VendorList->gst_number,
                'status' => $VendorList->is_active == 1
                    ? '<span onclick=\'statusChange("' . $VendorList->id . '", 0)\'><div class="badge badge-success" style="cursor:pointer">Active</div></span>'
                    : '<span onclick=\'statusChange("' . $VendorList->id . '", 1)\'><div class="badge badge-danger" style="cursor:pointer">Inactive</div></span>',

                'created_at' => date('d-m-Y h:i', strtotime($VendorList->created_at)),
                'updated_at' => date('d-m-Y h:i', strtotime($VendorList->updated_at)),
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
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vendor_lists,name',
            'email' => 'nullable|email|max:255|unique:vendor_lists,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            // 'gst_number' => 'required'
        ]);

        // add created_by for vendor
        $data['created_by'] = auth()->id();
        $type = $request->has('type') ? 'main' : 'local';

        $vendor = VendorList::create($data);

        // Ledger validation (same as your code)
        $ledgerData = [
            'name'            => $vendor->name,   // ledger name = vendor name
            'group_id'        => 20,               // example: Sundry Creditors group
            'branch_id'       => null,
            'opening_balance' => 0,
            'opening_type'    => 'Dr',
            'is_active'       => 1,
            'contact_details' => $vendor->phone,
            'type' => $type,
        ];

        // validate ledger fields
        $validatedLedger = validator($ledgerData, [
            'name'            => 'required|string|max:191|unique:account_ledgers,name',
            'group_id'        => 'required|exists:account_groups,id',
            'branch_id'       => 'nullable|integer|exists:branches,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_type'    => 'required|in:Dr,Cr',
            'is_active'       => 'nullable|boolean',
            'contact_details' => 'nullable|string',
        ])->validate();

        // Create ledger
        AccountLedger::create($validatedLedger);

        return redirect()->route('vendor.list')->with('success', 'Vendor has been succesfully created.');
    }

    public function edit($id)
    {
        $vendor = VendorList::where('id', $id)->firstOrFail();
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vendor_lists,name,' . $request->id,
            'email' => 'nullable|email|max:255|unique:vendor_lists,email,' . $request->id,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $vendor = VendorList::findOrFail($request->id);

        $oldName = $vendor->name; // important for ledger search

        // Update vendor
        $vendor->name = $request->name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->address = $request->address;
        $vendor->gst_number = $request->gst_number;
        $vendor->type = $request->type;
        $vendor->updated_by = auth()->id();
        $vendor->save();

        // Detect type same as store()
        $type = $request->has('type') ? 'main' : 'local';

        // Find ledger (based on old name)
        $ledger = AccountLedger::where('name', $oldName)->first();

        if ($ledger) {

            // Prepare ledger update data
            $ledgerData = [
                'name'            => $vendor->name,
                'group_id'        => 5,
                'branch_id'       => null,
                'opening_balance' => $ledger->opening_balance ?? 0,
                'opening_type'    => $ledger->opening_type ?? 'Dr',
                'is_active'       => 1,
                'contact_details' => $vendor->phone,
                'type'            => $type,
            ];

            // Validate ledger (ignore current ledger id)
            $validatedLedger = validator($ledgerData, [
                'name'            => 'required|string|max:191|unique:account_ledgers,name,' . $ledger->id,
                'group_id'        => 'required|exists:account_groups,id',
                'branch_id'       => 'nullable|integer|exists:branches,id',
                'opening_balance' => 'nullable|numeric|min:0',
                'opening_type'    => 'required|in:Dr,Cr',
                'is_active'       => 'nullable|boolean',
                'contact_details' => 'nullable|string',
            ])->validate();

            // Update ledger
            $ledger->update($validatedLedger);
        }

        return redirect()->route('vendor.list')->with('success', 'Vendor has been successfully updated');
    }

    public function destroy($id)
    {

        $tras = Purchase::where('vendor_id', $id)->first();

        if (!empty($tras)) {
            return response()->json(['status' => 'error', 'message' => "This vendor user can'n delete."]);
        }
        // Find the user and soft delete
        $record = VendorList::findOrFail($id);
        $record->delete();
        return response()->json(['success' => true, 'message' => 'Vendor has been deleted successfully.']);
    }

    public function statusChange(Request $request)
    {
        $user = VendorList::findOrFail($request->id);
        $user->is_active = $request->status;
        $user->save();

        return response()->json(['message' => 'Vendor Status has been changed successfully']);
    }
}
