<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Accounting\AccountLedger;
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
        $draw              = (int) $request->input('draw', 1);
        $start             = (int) $request->input('start', 0);
        $length            = (int) $request->input('length', 10);
        $searchValue       = (string) $request->input('search.value', '');
        $orderColumnIndex  = (int) $request->input('order.0.column', 0);
        $orderDirection    = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $orderColumn       = (string) $request->input("columns.$orderColumnIndex.data", 'created_at');

        // Whitelist orderable columns to avoid SQL injection
        $sortable = [
            'title'        => 'expenses.title',
            'title'        => 'expenses.title',
            'description'  => 'expenses.description',
            'ledger_name'  => 'ledger_name',
            'amount'       => 'expenses.amount',
            'branch_name'  => 'branch_name',
            'user_name'    => 'user_name',
            'created_at'   => 'expenses.created_at',
        ];
        $orderBy = $sortable[$orderColumn] ?? 'expenses.created_at';

        // If your FK column is different, change 'account_ledger_id' below to your column (e.g., 'ledger_id')
        $query = \DB::table('expenses')
            ->leftJoin('account_ledgers as l', 'expenses.expense_category_id', '=', 'l.id')
            ->leftJoin('branches as b', 'expenses.branch_id', '=', 'b.id')
            ->leftJoin('users as u', 'expenses.user_id', '=', 'u.id')
            ->select([
                'expenses.id',
                'expenses.title',
                'expenses.description',
                'expenses.amount',
                'expenses.expense_date',
                'expenses.created_at',
                \DB::raw('COALESCE(l.name, "-") as ledger_name'),
                \DB::raw('COALESCE(b.name, "-") as branch_name'),
                \DB::raw('COALESCE(u.name, "-") as user_name'),
            ]);

        // Search
        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('expenses.title', 'like', "%{$searchValue}%")
                    ->orWhere('expenses.description', 'like', "%{$searchValue}%")
                    ->orWhere('l.name', 'like', "%{$searchValue}%")
                    ->orWhere('b.name', 'like', "%{$searchValue}%")
                    ->orWhere('u.name', 'like', "%{$searchValue}%");
            });
        }

        // Totals
        $recordsTotal    = \DB::table('expenses')->count();
        $recordsFiltered = (clone $query)->count();
        $roleId = auth()->user()->role_id;

        $userId = auth()->id();

        $listAccess = getAccess($roleId, 'expense-manage');

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
        // Page
        $rows = $query->orderBy($orderBy, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        // Build response data
        $data = $rows->map(function ($e) {
            // $ownerId = $g->created_by;  // If available
            // if (canDo($roleId, 'product-edit', $ownerId)) {
            // }
            return [
                'title'        => $e->title,
                'description'  => \Illuminate\Support\Str::limit(strip_tags($e->description), 50, '...')
                    . ' <a href="#" class="text-primary view-desc" data-desc="' . e($e->description) . '">View</a>',
                'ledger_name'  => $e->ledger_name,
                'amount'       => number_format((float)$e->amount, 2),
                'branch_name'  => $e->branch_name,
                'user_name'    => $e->user_name,
                'created_at'   => \Carbon\Carbon::parse($e->created_at)->format('d-m-Y  h:i A'),
                'action'       => '', // add buttons if needed
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ExpenseCategory::where('status', true)->pluck('name', 'id');
        $directNames   = ['Direct Expenses', 'Direct Expense', 'Expense - Direct'];
        $indirectNames = ['Indirect Expenses', 'Indirect Expense', 'Expense - Indirect'];

        $expense = AccountLedger::query()
            ->leftJoin('account_groups as g', 'g.id', '=', 'account_ledgers.group_id')
            ->whereIn('g.name', array_merge($directNames, $indirectNames))
            ->where(function ($q) {
                $q->where('account_ledgers.is_deleted', 'No')->orWhereNull('account_ledgers.is_deleted');
            })
            ->where(function ($q) {
                $q->where('account_ledgers.is_active', 1)->orWhere('account_ledgers.is_active', 'Yes');
            })
            ->orderBy('account_ledgers.name')
            ->pluck('account_ledgers.name', 'account_ledgers.id')
            ->toArray();

        return view('expenses.create', compact('categories', 'expense'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:account_ledgers,id',
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
