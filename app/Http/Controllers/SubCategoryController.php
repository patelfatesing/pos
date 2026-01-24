<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function index()
    {
        $subCategories = SubCategory::with('category')->get();
        $categories = Category::all();
        return view('subcategories.index', compact('subCategories', 'categories'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'sub_categories.id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'asc';
        }

        $query = SubCategory::select(
            'sub_categories.id',
            'sub_categories.name',
            'categories.name as category_name',
            'sub_categories.is_active',
            'sub_categories.created_at',
            'sub_categories.updated_at'
        )
            ->join('categories', 'sub_categories.category_id', '=', 'categories.id')
            ->where('sub_categories.is_deleted', 'no');

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('sub_categories.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('categories.name', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = SubCategory::where('is_deleted', 'no')->count();
        $recordsFiltered = $query->count();
        $roleId = auth()->user()->role_id;

        $userId = auth()->id();

        $listAccess = getAccess($roleId, 'sub-categories-list');

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

        foreach ($data as $row) {
            // $ownerId = $role->created_by;  // If available

            // $action = '';
            // if (canDo($roleId, 'sub-categories-edit', $ownerId)) {
            $action = '<div class="d-flex align-items-center list-action">   
                        <button class="badge bg-success mr-2 border-0" onclick="editSubCategory(' . $row->id . ')" title="Edit">
                        <i class="ri-pencil-line"></i>
                    </button>';
            // }

            $records[] = [
                'name' => $row->name,
                'category_name' => $row->category_name,
                'is_active' => $row->is_active == 'yes'
                    ? '<span onclick=\'statusChange("' . $row->id . '", "no")\'><div class="badge badge-success" style="cursor:pointer">Active</div></span>'
                    : '<span onclick=\'statusChange("' . $row->id . '", "yes")\'><div class="badge badge-danger" style="cursor:pointer">Inactive</div></span>',
                'created_at' => \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i'),
                'updated_at' => \Carbon\Carbon::parse($row->updated_at)->format('d-m-Y H:i'),
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
        $categories = Category::all();
        return view('subcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255'
        ]);

        // If validation fails, return JSON errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create record
        SubCategory::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'is_active' => 1,
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sub Category created successfully!'
        ]);
    }

    public function edit($id)
    {
        // $categories = Category::all();

        // $record = SubCategory::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        // return view('subcategories.edit', compact('record', 'categories'));
        $sub = SubCategory::findOrFail($id);
        return response()->json($sub);
    }

    public function update(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'sometimes|string|max:255|unique:sub_categories,name,' . $request->id,
        ], [
            'name.unique' => 'This subcategory name already exists.',
            'name.max' => 'The subcategory name must not exceed 255 characters.',
            'category_id.required' => 'Please select a category.',
        ]);


        $sub_category = subCategory::findOrFail($request->id);
        $sub_category->category_id = $request->category_id;
        $sub_category->name = $request->name;
        $sub_category->updated_by = auth()->id();
        $sub_category->save();


        return redirect()->route('subcategories.list')->with('success', 'SubCategory updated!');
    }

    public function destroy(Request $request)
    {
        $id = $request->id;
        // Find the user and soft delete
        $record = SubCategory::findOrFail($id);
        $record->update(['is_deleted' => 'yes']);

        return redirect()->route('users.list')->with('success', 'Sub Category has been deleted successfully.');
    }

    public function statusChange(Request $request)
    {
        $user = SubCategory::findOrFail($request->id);
        $user->is_active = $request->status;
        $user->save();

        return response()->json(['message' => 'Status updated successfully']);
    }
}
