<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Roles;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

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

    public function changePassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true]);
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

        $query = User::select('users.*', 'first_name', 'last_name', 'branches.name as branch_name', 'roles.name as role_name', 'user_info.phone_number')
            ->leftJoin('user_info', 'users.id', '=', 'user_info.user_id')
            ->leftJoin('branches', 'user_info.branch_id', '=', 'branches.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')->where('users.is_deleted', '!=', 'yes');


        if ($request->has('status') && $request->status != '') {
            $query->where('users.is_active', $request->status);
        }

        // **Search filter**
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('users.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.email', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.username', 'like', '%' . $searchValue . '%')
                    ->orWhere('branches.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('roles.name', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = User::where('is_deleted', '!=', 'yes')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        $url = url('/');
        foreach ($data as $employee) {

            $action = "";
            // $action .= "<a href='" . $url . "/users/edit/" . $employee->id . "' class='btn btn-info mr_2'>Edit</a>";
            // $action .= '<button type="button" onclick="delete_user(' . $employee->id . ')" class="btn btn-danger ml-2">Delete</button>';

            $action = '<div class="d-flex align-items-center list-action">
                                    <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="' . url('/users/edit/' . $employee->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                                        <button class="btn btn-sm btn-warning" onclick="openChangePasswordModal(' . $employee->id . ')">Change Password</button>

                                        </div>';

            $records[] = [
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'username' => $employee->username,
                'email' => $employee->email,
                'phone_number' => $employee->phone_number,
                'role_name' => $employee->role_name,
                'branch_name' => $employee->branch_name,
                'is_active' => $employee->is_active == 'yes'
                    ? '<span onclick=\'statusChange("' . $employee->id . '", "no")\'><div class="badge badge-success" style="cursor:pointer">Active</div></span>'
                    : '<span onclick=\'statusChange("' . $employee->id . '", "yes")\'><div class="badge badge-danger" style="cursor:pointer">Inactive</div></span>',
                'created_at' => $employee->created_at->format('d-m-Y h:i A'),
                'updated_at' => $employee->updated_at->format('d-m-Y h:i A'),
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
        $roles = Roles::where('is_deleted', 'no')->pluck('name', 'id');
        $branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');
        return view('user.create', compact('roles', 'branch'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'phone_number' => [
                'required',
                'regex:/^(\+?\d{1,3})?\d{10}$/'
            ],
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array((int) $request->role_id, [3, 4]);
                }),
                'nullable',
                'exists:branches,id',
            ],
        ], [
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.regex' => 'Please enter a valid phone number.',
            'role_id.required' => 'Please select role.',
            'branch_id.required' => 'Please select store.',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'created_by' => $request->created_by
        ]);

        // Create user info
        UserInfo::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'user_id' => $user->id,
            'branch_id' => $request->branch_id,
            'address' => $request->address,
            'phone_number' => $request->phone_number
        ]);

        return redirect()->route('users.list')->with('success', 'User has been created');
    }

    // Show edit form
    public function edit($id)
    {
        $roles = Roles::where('is_deleted', 'no')->pluck('name', 'id');
        $branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');
        $record = User::with(['userInfo'])->where('users.id', $id)->where('is_deleted', 'no')->firstOrFail();

        return view('user.edit', compact('record', 'roles', 'branch'));
    }

    // Update a record
    public function update(Request $request)
    {
        $id = $request->id;

        // Validate the request
        $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $id,
            'phone_number'  => [
                'required',
                'regex:/^(\+?\d{1,3})?\d{10}$/'
            ],
            'role_id'       => 'required|exists:roles,id',
            'branch_id' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array((int) $request->role_id, [3, 4]);
                }),
                'nullable',
                'exists:branches,id',
            ],
        ], [
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.regex'    => 'Please enter a valid phone number.',
            'role_id.required'      => 'Please select role.',
            'branch_id.required'    => 'Please select store.',
        ]);

        // Find the user (only if not deleted)
        $user = User::where('id', $id)->where('is_deleted', 'no')->firstOrFail();

        // Update user main table
        $user->update([
            'name'    => $request->first_name . ' ' . $request->last_name,
            'role_id' => $request->role_id,
            'username' => $request->username,
            // 'updated_by' => $request->updated_by, // Optional if you have this
        ]);

        // Update user info relation
        $user->userInfo()->update([
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'branch_id'    => $request->branch_id,
            'address'      => $request->address,
            'phone_number' => $request->phone_number,
        ]);

        return redirect()->route('users.list')->with('success', 'Record updated successfully.');
    }

    // Soft delete a record
    public function destroy(Request $request)
    {
        $id = $request->id;
        // Find the user and soft delete
        $record = User::findOrFail($id);
        $record->update(['is_deleted' => 'yes']);

        return redirect()->route('users.list')->with('success', 'Record deleted successfully.');
    }

    public function statusChange(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->is_active = $request->status;
        $user->is_login = 'No';
        $user->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    // public function openDrawer()
    // {
    //     try {
    //         // Connect to the shared printer
    //         $connector = new WindowsPrintConnector("smb://localhost/TVSPrinter"); // Replace with your shared printer name
    //         $printer = new Printer($connector);

    //         // Open the drawer using pulse command
    //         $printer->pulse(); // Sends the default drawer pulse

    //         $printer->close();

    //         return response()->json(['status' => 'Drawer opened']);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'Error', 'message' => $e->getMessage()], 500);
    //     }
    // }
}
