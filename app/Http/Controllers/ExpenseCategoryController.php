<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\ExpenseMainCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::latest()->paginate(10);
        $expMainCategory = ExpenseMainCategory::get();
        return view('expense_categories.index', compact('categories', 'expMainCategory'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'asc';
        }

        $query = ExpenseCategory::with('expenseType')
            ->where('status', 1);

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
        foreach ($data as $role) {
            $action = '<div class="d-flex align-items-center list-action">
                        <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="Edit"
                            href="' . url('/exp-category/edit/' . $role->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                    </div>';

            $records[] = [
                'name' => $role->name,
                'expense_type' => $role->expenseType->name ?? '<span class="badge bg-secondary">N/A</span>',
                'is_active' => ($role->status == 1
                    ? '<div class="badge badge-success">Active</div>'
                    : '<div class="badge badge-danger">Inactive</div>'),
                'created_at' => $role->created_at ? $role->created_at->format('d-m-Y H:i') : '',
                'updated_at' => $role->updated_at ? $role->updated_at->format('d-m-Y H:i') : '',
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
            'name' => 'required|unique:expense_categories,name'
        ]);

        ExpenseCategory::create([
            'name' => $request->name,
            'expense_type_id' => $request->expense_type_id,
            'slug' => Str::slug($request->name),
            'status' => $request->status ?? true,
            'created_by' => auth()->id()
        ]);

        return response()->json(['message' => 'Expense Category created successfully.']);
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|unique:expense_categories,name',
    //         'description' => 'nullable',
    //         'status' => 'boolean',
    //     ]);

    //     ExpenseCategory::create([
    //         'name' => $request->name,
    //         'slug' => Str::slug($request->name),
    //         'description' => $request->description,
    //         'status' => $request->status ?? true,
    //         'created_by' => auth()->id()
    //     ]);

    //     return redirect()->route('exp_category.list')
    //         ->with('success', 'Expense ExpenseCategory created successfully.');
    // }

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
