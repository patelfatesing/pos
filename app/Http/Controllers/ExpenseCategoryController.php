<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::latest()->paginate(10);
        return view('expense_categories.index', compact('categories'));
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

        $query = ExpenseCategory::query()->where('status', 1);

        if (!empty($searchValue)) {
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        $recordsTotal = ExpenseCategory::where('status', 1)->count();
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
                                        href="' . url('/exp-category/edit/' . $role->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                               </div>';
            
            $records[] = [
                'name' => $role->name,
                'is_active' =>($role->status == 1 ? '<div class="badge badge-success">Active</div>':'<div class="badge badge-success">Inactive</div>'),
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
        return view('expense_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:expense_categories,name',
            'description' => 'nullable',
            'status' => 'boolean',
        ]);

        ExpenseCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'status' => $request->status ?? true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('exp_category.list')
            ->with('success', 'Expense ExpenseCategory created successfully.');
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        return view('expense_categories.show', compact('expenseCategory'));
    }

    public function edit($id)
    {
        $record = ExpenseCategory::where('id', $id)->where('status', 1)->firstOrFail();
        
        return view('expense_categories.edit', compact('record'));
    }

    public function update(Request $request)
    {
        
        $id = $request->id;
        
        $expenseCategory = ExpenseCategory::findOrFail($id);
        
        $request->validate([
            'name' => 'required|unique:expense_categories,name,' . $id,
            'description' => 'nullable',
            'status' => 'boolean',
        ]);

        $expenseCategory->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'status' => $request->status ?? true,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('exp_category.list')
            ->with('success', 'Expense Category updated successfully.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        return redirect()->route('exp_category.list')
            ->with('success', 'xpense Category deleted successfully.');
    }
}
