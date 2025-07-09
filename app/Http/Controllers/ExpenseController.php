<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ExpenseCategory::latest()->paginate(10);
        return view('expenses.index', compact('categories'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'expenses.id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'asc';
        }

        $query = \DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->leftJoin('branches', 'expenses.branch_id', '=', 'branches.id')
            ->leftJoin('users', 'expenses.user_id', '=', 'users.id')
            ->select(
                'expenses.id',
                'expenses.title',
                'expenses.description',
                'expense_categories.name as category_name',
                'expenses.amount',
                'expenses.expense_date',
                'branches.name as branch_name',
                'users.name as user_name',
                'expenses.created_at'
            );

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('expenses.title', 'like', '%' . $searchValue . '%')
                    ->orWhere('expenses.description', 'like', '%' . $searchValue . '%')
                    ->orWhere('expense_categories.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('branches.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.name', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = \DB::table('expenses')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        foreach ($data as $expense) {
            $action = '';
            // $action = '<div class="d-flex align-items-center list-action">
            //             <a class="badge bg-success mr-2" data-toggle="tooltip" title="Edit"
            //                 href="' . url('/exp/edit/' . $expense->id) . '"><i class="ri-pencil-line"></i></a>
            //        </div>';

            $records[] = [
                'title' => $expense->title,
                'description' => Str::limit(strip_tags($expense->description), 50, '...') .
                    '<a href="#" class="text-primary view-desc" data-desc="' . e($expense->description) . '"> View</a>',
                'category_name' => $expense->category_name,
                'amount' => number_format($expense->amount, 2),
                'branch_name' => $expense->branch_name,
                'user_name' => $expense->user_name,
                'created_at' => \Carbon\Carbon::parse($expense->created_at)->format('d-m-Y  h:i A'),
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
        $categories = ExpenseCategory::where('status', true)->pluck('name', 'id');
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Expense::create($request->all());

        return redirect()->route('exp.list')
            ->with('success', 'Expense added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $expense = Expense::where('id', $id)->firstOrFail();
        $expense->expense_date = Carbon::parse($expense->expense_date);
        $categories = ExpenseCategory::where('status', true)->pluck('name', 'id');
        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $id = $request->id;

        $Expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $Expense->update($validated);

        return redirect()->route('exp.list')->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
