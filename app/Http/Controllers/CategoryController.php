<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
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

        $query = Category::query()->where('is_deleted', 'no');

        if (!empty($searchValue)) {
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        $recordsTotal = Category::where('is_deleted', 'no')->count();
        $recordsFiltered = $query->count();

        $roleId = auth()->user()->role_id;

        $userId = auth()->id();

        $listAccess = getAccess($roleId, 'categories-list');

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

        foreach ($data as $role) {
            // $ownerId = $role->created_by;  // If available

            // $action = '';
            // if (canDo($roleId, 'categories-edit', $ownerId)) {

                $action = '
                <div class="d-flex align-items-center list-action">

                    <button type="button" 
                        class="badge bg-success mr-2 border-0 edit-btn" 
                        data-toggle="tooltip" 
                        title="Edit" 
                        onclick="editCategory(' . $role->id . ')">
                        <i class="ri-pencil-line mr-0"></i>
                    </button>

                </div>';
            // }


            $records[] = [
                'name' => $role->name,
                'is_active' => ($role->is_active ? '<div class="badge badge-success">Active</div>' : '<div class="badge badge-success">Inactive</div>'),
                'created_at' => \Carbon\Carbon::parse($role->created_at)->format('d-m-Y H:i'),
                'updated_at' => \Carbon\Carbon::parse($role->updated_at)->format('d-m-Y H:i'),
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
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|unique:categories,name'
        ]);

        // If validation fails → return JSON errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // Create category
        Category::create([
            'name' => $request->name,
            'is_active' => 1,
            'created_by' => auth()->id()
        ]);

        // Return success JSON (handled in JS)
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully!'
        ]);
    }

    // public function edit($id)
    // {
    //     $record = Category::where('id', $id)->where('is_deleted', 'no')->firstOrFail();

    //     return view('categories.edit', compact('record'));
    // }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request)
    {
        $id = $request->id;

        $record = Category::findOrFail($id);

        // Manual validator so we can return JSON errors
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ], [
            'name.unique' => 'This category name already exists.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update record
        $record->update([
            'name' => $request->name,
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully!'
        ]);
    }


    public function destroy(Request $request)
    {
        $id = $request->id;
        // Find the user and soft delete
        $record = Category::findOrFail($id);
        $record->update(['is_deleted' => 'yes']);

        return redirect()->route('users.list')->with('success', 'Category has been deleted successfully.');
    }
}
