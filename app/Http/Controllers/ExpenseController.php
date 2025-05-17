<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;

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

        // Start from expenses and join expense_categories
        $query = \DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select(
                'expenses.id',
                'expenses.title',
                'expense_categories.name as category_name',
                'expenses.amount',
                'expenses.expense_date',
                'expenses.created_at'
            );

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('expenses.title', 'like', '%' . $searchValue . '%')
                ->orWhere('expense_categories.name', 'like', '%' . $searchValue . '%');
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

            $action = '<div class="d-flex align-items-center list-action">
                            <a class="badge bg-success mr-2" data-toggle="tooltip" title="Edit"
                                href="' . url('/exp/edit/' . $expense->id) . '"><i class="ri-pencil-line"></i></a>
                    </div>';

            $records[] = [
                'title' => $expense->title,
                'category_name' => $expense->category_name,
                'amount' => number_format($expense->amount, 2),
                'expense_date' => \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y'),
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
        return view('expenses.edit', compact('expense','categories'));
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
