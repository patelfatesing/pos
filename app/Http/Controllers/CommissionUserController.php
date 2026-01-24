<?php

namespace App\Http\Controllers;

use App\Models\Commissionuser;
use App\Models\CommissionUserImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;

class CommissionUserController extends Controller
{
    public function index()
    {
        $commissionUsers = Commissionuser::with('images')->latest()->get();
        return view('commission_users.index', compact('commissionUsers'));
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

        // $query = Commissionuser::with('images')->where('status', 'Active');
        $query = Commissionuser::with('images');
        // **Search filter**
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('first_name', 'like', '%' . $searchValue . '%')
                    // ->orWhere('last_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('commission_type', 'like', '%' . $searchValue . '%')
                    ->orWhere('applies_to', 'like', '%' . $searchValue . '%')
                    ->orWhere('commission_value', 'like', '%' . $searchValue . '%');
            });
        }

        $query->where('is_deleted', 'no');

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $recordsTotal = Commissionuser::count();
        $recordsFiltered = $query->count();

        // pagination: only when length > 0
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $roleId = auth()->user()->role_id;

        $userId = auth()->id();

        $listAccess = getAccess($roleId, 'commission-customer-manage');

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

        $data = $query->orderBy($orderColumn, $orderDirection)->get();
        // $data = $query->orderBy($orderColumn, $orderDirection)
        //     ->offset($start)
        //     ->limit($length)
        //     ->get();

        $records = [];

        foreach ($data as $commissionUser) {
            $ownerId = $commissionUser->created_by;  // If available
            $images = $commissionUser->images->map(function ($image) {
                return asset('storage/' . $image->image_path);
            })->toArray();

            // $first_name = '<div class="d-flex align-items-center list-action"><a class="badge bg-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" href="' . url('/commission-cust/view/' . $commissionUser->id) . '">' . $commissionUser->first_name . '</a></div>';
            // $last_name = '<div class="d-flex align-items-center list-action"><a class="badge bg-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit" href="' . url('/commission-cust/view/' . $commissionUser->id) . '">' . $commissionUser->last_name . '</a></div>';


            $action = '<div class="d-flex align-items-center list-action">';
            // if (canDo($roleId, 'commission-customer-edit', $ownerId)) {
                $action .= '<a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="' . url('/commission-users/edit/' . $commissionUser->id) . '"><i class="ri-pencil-line mr-0"></i></a>';
            // }
            $action .= '<a class="badge bg-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="' . url('/commission-cust/view/' . $commissionUser->id) . '"><i class="ri-eye-line mr-0"></i></a>';
            // if (canDo($roleId, 'commission-customer-delete', $ownerId)) {
                $action .= '<a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#" onclick="delete_commission_user(' . $commissionUser->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>';
            // }
            $action = '</div>';

            // $action = '<div class="d-flex align-items-center list-action">
            //                         <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
            //                             href="' . url('/commission-users/edit/' . $commissionUser->id) . '"><i class="ri-pencil-line mr-0"></i></a>
            //                         <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
            //                             href="#" onclick="delete_commission_user(' . $commissionUser->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>
            // </div>';

            $records[] = [
                'first_name' => $commissionUser->first_name,
                'commission_type' => $commissionUser->commission_type,
                'commission_value' => $commissionUser->commission_value,
                'mobile_number' => $commissionUser->mobile_number,
                'applies_to' => $commissionUser->applies_to,
                'is_active' => $commissionUser->is_active,
                'start_date' => $commissionUser->start_date,
                'end_date' => $commissionUser->end_date,
                'images' => implode(', ', array_map(function ($image) {
                    return "<img src='{$image}' alt='Image' style='width:50px;height:50px;'>";
                }, $images)),
                'is_active' => $commissionUser->is_active == 1
                    ? '<span onclick=\'statusChange("' . $commissionUser->id . '", 0)\'><div class="badge badge-success" style="cursor:pointer">Active</div></span>'
                    : '<span onclick=\'statusChange("' . $commissionUser->id . '", 1)\'><div class="badge badge-danger" style="cursor:pointer">Inactive</div></span>',
                //'is_deleted' => ($commissionUser->is_deleted=="No" ? '<div class="badge badge-success">Not Deleted</div>' : '<div class="badge badge-danger">Deleted</div>'),
                'created_at' => date('d-m-Y h:i', strtotime($commissionUser->created_at)),
                'updated_at' => date('d-m-Y h:i', strtotime($commissionUser->updated_at)),
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
        return view('commission_users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255|unique:commission_users,first_name',
            'email' => 'nullable|email|max:255|unique:commission_users,email',
            'mobile_number' => 'nullable|digits:10|regex:/^[0-9]+$/|unique:commission_users,mobile_number',
            'commission_type' => 'required|in:fixed,percentage',
            // 'applies_to' => 'required|in:all,category,product',
            'reference_id' => 'nullable|string',
            'is_active' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg'
        ], [
            'first_name.required' => 'Commission user name is required.',
            'first_name.unique' => 'Commission User has already been taken.'
        ]);

        // if ($request->hasFile('image')) {
        //     $imagePath = $request->file('image')->store('commission_images', 'public');
        //     $data['photo'] = $imagePath;
        // }

        $commissionUser = Commissionuser::create($data);
        if ($request->hasFile('image')) {
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = $commissionUser->id . '_commissionuser.' . $extension;

            // Store new photo
            $photoPath = $request->file('image')->storeAs('commission_photos', $filename, 'public');

            // Update commissionUser photo field
            $commissionUser->photo = $photoPath;
            $commissionUser->created_by = auth()->id();
            $commissionUser->save();
        }
        return redirect()->route('commission-users.list')->with('success', 'Commission User Created');
    }

    public function edit($id)
    {
        $commissionUser = Commissionuser::with('images')->where('id', $id)->firstOrFail();
        return view('commission_users.edit', compact('commissionUser'));
    }

    public function view($id)
    {
        $commissionUser = Commissionuser::with('images')->where('id', $id)->firstOrFail();
        return view('commission_users.view', compact('commissionUser'));
    }

    public function custTrasactionPhoto($id)
    {
        $photos = CommissionUserImage::select('image_path', 'type', 'id')->where('transaction_id', $id)->first();


        return view('commission_users.cust-photo', compact('photos'));

        return response()->json(['error' => 'Form not found'], 404);
    }

    public function getDataCommission(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir', 'desc');

        $columns = [
            'invoice_id',
            'invoice_number',
            'invoice_date',
            'invoice_total',
            'commission_amount',
            'commission_user_id',
            'commission_user_name',
            'commission_type',
            'commission_value',
            'applies_to',
            'start_date',
            'end_date',

        ];

        $orderColumn = $columns[$orderColumnIndex] ?? 'invoice_date';
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        // Base query
        $query = DB::table('invoices as i')
            ->select(
                'i.id as invoice_id',
                'i.invoice_number',
                'i.created_at as invoice_date',
                'i.total as invoice_total',
                'cu.id as commission_user_id',
                DB::raw("CONCAT(cu.first_name, ' ', cu.last_name) as commission_user_name"),
                'ch.total_purchase_items',
                'ch.discount_amount',
                'ch.id as commission_id',
                'pi.image_path',
                'pi.id as commission_user_image_id',
                'pi.type',
            )
            ->leftJoin('discount_histories as ch', 'i.id', '=', 'ch.invoice_id')
            ->leftJoin('commission_users as cu', 'ch.commission_user_id', '=', 'cu.id')
            ->leftJoin('commission_user_images as pi', 'i.id', '=', 'pi.transaction_id')
            ->whereNotNull('i.commission_user_id');

        // Total record count before filters
        $recordsTotal = DB::table('invoices')
            ->whereNotNull('commission_user_id')
            ->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('i.invoice_number', 'like', "%$searchValue%")
                    ->orWhere(DB::raw("CONCAT(cu.first_name, ' ', cu.last_name)"), 'like', "%$searchValue%");
            });
        }

        if ($request->customer_id) {
            $query->where('cu.id', $request->customer_id);
        }

        // Count after filters
        $recordsFiltered = $query->count();

        // Get data with order and pagination
        $data = $query
            ->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function statusChange(Request $request)
    {
        $user = Commissionuser::findOrFail($request->id);
        $user->is_active = $request->status;
        $user->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function update(Request $request, Commissionuser $Commissionuser)
    {
        $data = $request->validate([

            'first_name' => 'required|string|max:255|unique:commission_users,first_name,' . $Commissionuser->id,
            'mobile_number' => 'nullable|digits:10|regex:/^[0-9]+$/|unique:commission_users,mobile_number,' . $Commissionuser->id,
            'commission_type' => 'required|in:fixed,percentage',
            // 'applies_to' => 'required|in:all,category,product',
            'reference_id' => 'nullable|string',
            'is_active' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg',
        ], [
            'first_name.required' => 'Commission user name is required.',
            'first_name.unique' => 'Commission User has already been taken.'
        ]);

        // Handle new photo upload
        // if ($request->hasFile('photo')) {
        //     // Delete old photo
        //     if ($Commissionuser->photo && Storage::disk('public')->exists($Commissionuser->photo)) {
        //         Storage::disk('public')->delete($Commissionuser->photo);
        //     }

        //     // Store new photo
        //     $data['photo'] = $request->file('photo')->store('commission_photos', 'public');
        // }

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($Commissionuser->photo) {
                \Storage::disk('public')->delete($Commissionuser->photo);
            }
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filename = $Commissionuser->id . '_commissionuser' . '.' . $extension;

            // Store new photo
            $data['photo'] = $request->file('photo')->storeAs('commission_photos', $filename, 'public');
        }
        $Commissionuser->update($data);

        return redirect()->route('commission-users.list')->with('success', 'Commission User Updated');
    }

    public function destroy(Request $request)
    {
        $id =  $request->id;
        $tras = Invoice::where('commission_user_id', $id)->first();

        if (!empty($tras)) {
            return response()->json(['status' => 'error', 'message' => "This Party user can'n delete."]);
        }

        $record = Commissionuser::findOrFail($id);
        $record->is_deleted = "Yes";
        $record->save();
        //$Commissionuser->delete();
        return response()->json(['success' => true, 'message' => 'Commission User has been deleted successfully.']);
    }
}
