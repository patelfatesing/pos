<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Roles;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Hash;

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

        $query = User::select('users.*', 'first_name','last_name','branches.name as branch_name', 'roles.name as role_name')
        ->leftJoin('user_info', 'users.id', '=', 'user_info.user_id')
        ->leftJoin('branches', 'user_info.branch_id', '=', 'branches.id')
        ->leftJoin('roles', 'users.role_id', '=', 'roles.id')->where('users.is_deleted', '!=', 'yes');
    
    // **Search filter**
    if (!empty($searchValue)) {
        $query->where(function ($q) use ($searchValue) {
            $q->where('users.name', 'like', '%' . $searchValue . '%')
              ->orWhere('users.email', 'like', '%' . $searchValue . '%')
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
            $action .= "<a href='" . $url . "/users/edit/" . $employee->id . "' class='btn btn-info mr_2'>Edit</a>";
            $action .= '<button type="button" onclick="delete_user(' . $employee->id . ')" class="btn btn-danger ml-2">Delete</button>';

            $records[] = [
                'name' => $employee->first_name.' '.$employee->last_name,
                'email' => $employee->email,
                'phone_number' => $employee->phone_number,
                'role_name' => $employee->role_name,
                'branch_name' => $employee->branch_name,
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
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => [
                'required',
                'regex:/^(\+?\d{1,3})?\d{10}$/'
            ],
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'required|exists:branch,id',
        ], [
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.regex' => 'Please enter a valid phone number.',
            'role_id.required' => 'Please select role.',
            'branch_id.required' => 'Please select store.'
        ]);

         // Create the user
         $user = User::create([
            'name' => $request->first_name.' '.$request->last_name,
            'email' => $request->email,
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

        return redirect()->route('users.list')->with('success', 'Record created successfully.');
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
        $roles = Roles::where('is_deleted', 'no')->pluck('name', 'id');
        $branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');
        $record = User::with(['userInfo'])->where('users.id', $id)->where('is_deleted', 'no')->firstOrFail();
        
        return view('user.edit', compact('record','roles','branch'));
    }

    // Update a record
    public function update(Request $request)
    {
        $id = $request->id;
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'regex:/^(\+?\d{1,3})?\d{10}$/'
            ],
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
        ], [
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.regex' => 'Please enter a valid phone number.',
            'role_id.required' => 'Please select role.',
            'branch_id.required' => 'Please select store.'
        ]);
    

        // Find the user
        $user = User::findOrFail($id)->when('is_deleted', 'no');

        // Update user info
        $user->update([
            'name' => $request->first_name.' '.$request->last_name,
            'role_id' => $request->role_id,
            // 'updated_by' => $request->updated_by
        ]);

        // Update user info
        $user->userInfo()->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'branch_id' => $request->branch_id,
            'address' => $request->address,
            'phone_number' => $request->phone_number
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
}
