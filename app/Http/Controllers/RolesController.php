<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Module;
use App\Models\Submodule;
use App\Models\RolePermission;
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
            $action .= "<a href='" . $url . "/roles/view/" . $role->id . "' class='btn btn-primary mr-2'>View</a>";
            $action .= '<button type="button" onclick="delete_role(' . $role->id . ')" class="btn btn-danger ml-2">Delete</button>';

            $records[] = [
                'name' => $role->name,
                'is_active' => ($role->is_active ? '<div class="badge badge-success">Active</div>' : '<div class="badge badge-success">Inactive</div>'),
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
        // Validate role fields
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'is_active' => 'nullable|in:yes,no',
        ], [
            'name.required' => 'The role name is required.',
            'name.unique' => 'This role name already exists.',
            'name.max' => 'The role name must not exceed 255 characters.',
            'is_active.in' => 'The is_active field must be either "yes" or "no".',
        ]);

        // Create the role
        $role = Roles::create([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? 'yes',
        ]);

        // =================================================
        // COPY DEFAULT MODULES FOR THIS ROLE
        // =================================================

        // Get only default modules (role_id = null)
        $defaultModules = Module::whereNull('role_id')->get();



        foreach ($defaultModules as $module) {

            // Insert new module row for this role
            $newModule = Module::create([
                'role_id' => $role->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'is_active' => 'no', // default OFF
            ]);

            // Fetch default submodules for this module
            $defaultSubmodules = Submodule::whereNull('role_id')
                ->where('module_id', $module->id)
                ->get();

            // Copy each submodule for this role
            foreach ($defaultSubmodules as $sub) {
                Submodule::create([
                    'role_id' => $role->id,
                    'module_id' => $newModule->id,
                    'name' => $sub->name,
                    'slug' => $sub->slug,
                    'type' => $sub->type,
                    'is_active' => 'no', // default permission = no
                ]);
            }
        }

        return redirect()->route('roles.list')
            ->with('success', 'Role created successfully with default modules and permissions.');
    }

    /**
     * Display the specified resource.
     */
    // public function show(Roles $Roles)
    // {
    //     //
    // }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        // group permissions by module
        $perms = Permission::orderBy('name')->get()
            ->groupBy(function ($p) {
                return explode('.', $p->name)[0] ?? 'misc';
            });

        // currently assigned permission names for this role
        $current     = $role->permissions()->pluck('permissions.name')->toArray();

        // describe how each action should render
        $actionConfig = [
            // binary: Yes/No
            'enable' => ['type' => 'binary'],
            'create' => ['type' => 'binary'],

            // scoped: None/Own/All
            'listing' => ['type' => 'scoped'],
            'view'    => ['type' => 'scoped'],
            'update'  => ['type' => 'scoped'],
            'delete'  => ['type' => 'scoped'],
        ];

        // modules you want to show (left labels)
        $modules = [
            'inventory'        => 'Inventory',
            'stock_request'    => 'Manage Stock Requests',
            'stock_transfer'   => 'Manage Stock Transfers',
            'product'          => 'Manage Products',
            'category'        => 'Manage Categories',
            'sub_category'     => 'Manage Sub Categories',
            'pack_size'       => 'Manage Pack Sizes',
            'store_management' => 'Manage Store',
            'shift_management' => 'Manage Shifts',
            'users'            => 'Users',
            // 'role_permission'  => 'Role and Permission',
            // add more here...
        ];

        // permissions already attached to the role (names)
        $current = $role->permissions()->pluck('permissions.name')->toArray();


        return view('roles.permissions', compact('role', 'perms', 'current', 'actionConfig', 'modules', 'current'));
    }

    // Show edit form
    // public function edit($id)
    // {
    //     $record = Roles::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
    //     return view('roles.edit', compact('record'));
    // }


    public function edit($roleId)
    {
        // Get role
        $record = Roles::where('id', $roleId)
            ->where('is_deleted', 'no')
            ->firstOrFail();

        // Load ONLY modules for this role
        $modules = Module::where('role_id', $roleId)
            ->with(['submodules' => function ($query) use ($roleId) {
                $query->where('role_id', $roleId);
            }])
            ->get();

        // Load permissions from submodules (using is_active field)
        $permissions = Submodule::where('role_id', $roleId)
            ->pluck('is_active', 'id') // id = submodule_id
            ->toArray();

        return view('roles.edit', compact('modules', 'permissions', 'roleId', 'record'));
    }


    // Update a record
    public function update(Request $request)
    {
        $id = $request->id;

        // Validate role basic data
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'is_active' => 'required|in:yes,no',
        ]);

        // Update role record
        $role = Roles::findOrFail($id);
        $role->update($validated);

        // =============================
        //      HANDLE PERMISSIONS
        // =============================

        // Clear old permissions (optional)
        RolePermission::where('role_id', $id)->delete();

        if ($request->has('modules')) {

            foreach ($request->modules as $module_id => $value) {
                $is_active = $value === 'yes' ? 'yes' : 'no';

                Module::where('id', $module_id)
                    ->update(['is_active' => $is_active]);
            }
        }


        // If permissions exist in request
        if ($request->has('permissions')) {
            foreach ($request->permissions as $submodule_id => $permData) {

                // Ensure valid access value exists
                $access = $permData['access'] ?? 'no';

                RolePermission::updateOrCreate(
                    [
                        'role_id' => $id,
                        'submodule_id' => $submodule_id
                    ],
                    [
                        'access' => $access
                    ]
                );

                Submodule::where('id', $submodule_id)
                    ->where('role_id', $id)
                    ->update([
                        'is_active' => $access
                    ]);
            }
        }

        return redirect()->route('roles.list')->with('success', 'Role updated successfully.');
    }

    // Soft delete a record
    public function destroy($id)
    {
        $record = Roles::where('id', $id)->where('is_deleted', 'no')->firstOrFail();

        $record->update(['is_deleted' => 'yes']);

        return redirect()->route('roles.list')->with('success', 'Record deleted successfully.');
    }
}
