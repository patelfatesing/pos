<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index()
    {
        $subCategories = SubCategory::with('category')->get();
        return view('subcategories.index', compact('subCategories'));
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
            'sub_categories.created_at'
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

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];
        $url = url('/');

        foreach ($data as $row) {

            $action = '<div class="d-flex align-items-center list-action">   
                        <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="' . url('/subcategories/edit/' . $row->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                                    <a class="badge bg-warning mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"
                                        href="#" onclick="delete_sub_cat(' . $row->id . ')"><i class="ri-delete-bin-line mr-0"></i></a>
            </div>';

            $records[] = [
                'name' => $row->name,
                'category_name' => $row->category_name,
                'is_active' => $row->is_active,
                'created_at' => \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i'),
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
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255'
        ]);

        SubCategory::create($request->all());
        return redirect()->route('subcategories.list')->with('success', 'SubCategory created!');
    }

    public function edit($id)
    {
        $categories = Category::all();

        $record = SubCategory::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        return view('subcategories.edit', compact('record', 'categories'));
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
}
