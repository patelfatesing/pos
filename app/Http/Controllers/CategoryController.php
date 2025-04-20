<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

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

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];
        $url = url('/');

        foreach ($data as $role) {

            $action ='<div class="d-flex align-items-center list-action">
                                    <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                                        href="' . url('/categories/edit/' . $role->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                               </div>';
            
            $records[] = [
                'name' => $role->name,
                'is_active' => $role->is_active,
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


    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:categories'
        ]);
        

        Category::create($request->only('name'));
        return redirect()->route('categories.list')->with('success', 'Category created!');
    }

    public function edit($id)
    {
        $record = Category::where('id', $id)->where('is_deleted', 'no')->firstOrFail();
        
        return view('categories.edit', compact('record'));
    }

    public function update(Request $request)
    {
        $id = $request->id;
        
        $record = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:categories,name,' . $id . '|max:255'
        ], [
            'name.unique' => 'This role name already exists.'
        ]);

        $record->update($validated);

        return redirect()->route('categories.list')->with('success', 'Category updated!');
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
