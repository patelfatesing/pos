<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VendorList;
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

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        foreach ($data as $VendorList) {
            $action ='<div class="d-flex align-items-center list-action">
                    <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                    href="' . url('/vendor/edit/' . $VendorList->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                    <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                    href="#" onclick="delete_product(' . $VendorList->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>
            </div>';

            $records[] = [
                'name' => $VendorList->name,
                'email' => $VendorList->email,
                'phone' => $VendorList->phone,
                'gst_number' => $VendorList->gst_number,
                'created_at' => date('d-m-Y h:i', strtotime($VendorList->created_at)),
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
            'email' => 'required|email|max:255|unique:vendor_lists,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'gst_number' => 'required'
        ]);

        VendorList::create($data);

        return redirect()->route('vendor.list')->with('success', 'Vendor has been succesfully created.');
    }

    public function edit($id)
    {
        $vendor = VendorList::where('id', $id)->firstOrFail();
        return view('vendors.edit', compact('vendor'));
    }
    public function update(Request $request, VendorList $VendorList)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vendor_lists,name,' . $request->id,
            'email' => 'required|email|max:255|unique:vendor_lists,email,' . $request->id,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'gst_number' => 'required|string|max:255',
        ]);
        
        $VendorList->update($data);

        return redirect()->route('vendor.list')->with('success', 'Vendor has been succesfully updated');
    }


    public function destroy(VendorList $VendorList)
    {
        $VendorList->delete();
        return response()->json(['success' => true, 'message' => 'Commission User Deleted']);
    }
}
