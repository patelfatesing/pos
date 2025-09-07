<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountLedger;
use App\Models\Accounting\AccountGroup;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    public function index()
    {
        $ledgers = AccountLedger::with('group')
            ->orderBy('name')
            ->get();
        $groups   = AccountGroup::orderBy('name')->get(['id', 'name']);
        $branches = Branch::where('is_deleted', 'no')->orderBy('name')->get(['id', 'name']);

        return view('accounting.ledgers.index', compact('ledgers', 'groups', 'branches'));
    }

    public function getData(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue      = $request->input('search.value', '');
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderColumn      = $request->input("columns.$orderColumnIndex.data", 'name');
        $orderDirection   = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $base = \DB::table('account_ledgers')
            ->leftJoin('account_groups as g', 'g.id', '=', 'account_ledgers.group_id')
            ->leftJoin('branches as b', 'b.id', '=', 'account_ledgers.branch_id')
            ->where(function ($q) {
                // adjust if your schema uses tinyint(1) or NULL
                $q->where('account_ledgers.is_deleted', 0)
                    ->orWhereNull('account_ledgers.is_deleted');
            })
            ->select([
                'account_ledgers.id',
                'account_ledgers.name',
                'account_ledgers.opening_balance',
                'account_ledgers.opening_type',
                'account_ledgers.is_active',
                'account_ledgers.created_at',
                \DB::raw('COALESCE(g.name, "-") as group_name'),
                \DB::raw('COALESCE(b.name, "-") as branch_name'),
            ]);

        $recordsTotal = (clone $base)->count();

        if ($searchValue !== '') {
            $base->where(function ($q) use ($searchValue) {
                $q->where('account_ledgers.name', 'like', "%{$searchValue}%")
                    ->orWhere('g.name', 'like', "%{$searchValue}%")
                    ->orWhere('b.name', 'like', "%{$searchValue}%")
                    ->orWhere('account_ledgers.opening_type', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $sortable = [
            'name'            => 'account_ledgers.name',
            'group_name'      => 'group_name',
            'branch_name'     => 'branch_name',
            'opening_balance' => 'account_ledgers.opening_balance',
            'opening_type'    => 'account_ledgers.opening_type',
            'is_active'       => 'account_ledgers.is_active',
            'created_at'      => 'account_ledgers.created_at',
        ];
        $orderBy = $sortable[$orderColumn] ?? 'account_ledgers.created_at';
        $base->orderBy($orderBy, $orderDirection);

        // ⬇️ IMPORTANT: avoid OFFSET without LIMIT for length=-1 (“All”)
        if ($length === -1) {
            $rows = $base->get();
        } else {
            $rows = $base->offset($start)->limit($length)->get();
        }

        $data = $rows->map(function ($l) {
            $activeBadge = $l->is_active
                ? '<span class="badge bg-success">Yes</span>'
                : '<span class="badge bg-secondary">No</span>';

            $actions = '
            <div class="d-flex align-items-center gap-1">
              <a href="' . route('accounting.ledgers.edit', $l->id) . '" class="btn btn-sm btn-warning mr-1">Edit</a>
              <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $l->id . '">Delete</button>
            </div>
        ';

            return [
                'name'            => e($l->name),
                'group_name'      => e($l->group_name),
                'branch_name'     => e($l->branch_name),
                'opening_balance' => number_format((float)$l->opening_balance, 2),
                'opening_type'    => e($l->opening_type),
                'is_active'       => $activeBadge,
                'created_at'      => optional($l->created_at)->format('d-m-Y h:i A') ?? '-',
                'action'          => $actions,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function create()
    {
        $groups = AccountGroup::orderBy('name')->get();
        $ledgers = AccountLedger::with('group')->orderBy('name')->get();
        $branches = \App\Models\Branch::select('name', 'id')->get();

        return view('accounting.ledgers.create', compact('groups', 'ledgers', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:191|unique:account_ledgers,name',
            'group_id'        => 'required|exists:account_groups,id',
            'branch_id'       => 'nullable|integer|exists:branches,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_type'    => 'required|in:Dr,Cr',
            'is_active'       => 'nullable|boolean',
        ]);

        AccountLedger::create($data);

        return redirect()->route('accounting.ledgers.list')
            ->with('success', 'Ledger created successfully.');
    }

    public function edit($id)
    {
        $groups = AccountGroup::orderBy('name')->get();
        $ledgers = AccountLedger::with('group')->orderBy('name')->get();
        $branches = \App\Models\Branch::select('name', 'id')->get();

        $ledger = AccountLedger::where('id', $id)->orderBy('name')->firstOrFail();
        return view('accounting.ledgers.edit', compact('ledger', 'groups', 'ledgers', 'branches'));
    }

    public function update(Request $request)
    {

        $id = $request->id; // comes from hidden input or route

        $ledger = AccountLedger::findOrFail($id);
        $data = $request->validate([
            'name'            => 'required|string|max:191|unique:account_ledgers,name,' . $ledger->id,
            'group_id'        => 'required|exists:account_groups,id',
            'branch_id'       => 'nullable|integer|exists:branches,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_type'    => 'required|in:Dr,Cr',
            'is_active'       => 'nullable|boolean',
        ]);

        $ledger->update($data);

        return redirect()->route('accounting.ledgers.list')
            ->with('success', 'Ledger updated successfully.');
    }

    public function destroy(Request $request)
    {
        $ledger = AccountLedger::where('id', $request->id)->firstOrFail();
        if ($ledger->lines()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete: ledger has transactions.']);
        }
        $ledger->is_deleted = true;
        $ledger->updated_by = auth()->id();
        $ledger->save();
        return response()->json(['success' => true, 'message' => 'Ledger has been deleted successfully.']);
    }
}
