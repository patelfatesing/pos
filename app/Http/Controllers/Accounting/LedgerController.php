<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountLedger;
use App\Models\Accounting\AccountGroup;
use App\Models\Accounting\Voucher;
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

            $viewUrl = route('accounting.ledgers.vouchers', $l->id);

            // Ledger name now clickable
            $nameLink = '<a href="' . e($viewUrl) . '" class="text-primary fw-bold">'
                . e($l->name) .
                '</a>';

            $actions = '
            <div class="d-flex align-items-center gap-1">
             <a href="' . e($viewUrl) . '" class="btn btn-sm btn-info mr-1">View</a>
              <a href="' . route('accounting.ledgers.edit', $l->id) . '" class="btn btn-sm btn-warning mr-1">Edit</a>
              <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $l->id . '">Delete</button>
            </div>
        ';

            return [
                'name'            => $nameLink,
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

    public function create($type = null)
    {
        $groups = AccountGroup::orderBy('name')->get();
        $ledgers = AccountLedger::with('group')->orderBy('name')->get();
        $branches = \App\Models\Branch::select('name', 'id')->get();

        return view('accounting.ledgers.create', compact('groups', 'ledgers', 'branches', 'type'));
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
            'contact_details' => 'nullable|string', // ← new
        ]);

        AccountLedger::create($data);

        if ($request->type == 'voucher') {
            return redirect()->route('accounting.vouchers.create')
                ->with('success', 'Party Ledger created successfully.');
        }elseif ($request->type == 'purchase') {
            return redirect()->route('purchase.create')
                ->with('success', 'Party Ledger created successfully.');
        }

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
            'contact_details' => 'nullable|string'
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

    // Show the ledger voucher view (blade)
    public function vouchers(AccountLedger $ledger, Request $request)
    {
        // default date range (same day)
        $start = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end   = $request->end_date   ?? now()->endOfMonth()->toDateString();

        // distinct voucher types for filter dropdown (adjust column name if different)
        $voucherTypes = Voucher::select('voucher_type')->distinct()->pluck('voucher_type');

        return view('accounting.ledgers.vouchers', compact('ledger', 'start', 'end', 'voucherTypes'));
    }

    public function vouchersData(AccountLedger $ledger, Request $request)
    {
        $start = $request->start_date ?? now()->startOfMonth()->toDateString();
        $end   = $request->end_date   ?? now()->endOfMonth()->toDateString();
        $vchType = $request->vch_type;

        // 1) Opening balance — use vouchers.voucher_date < $start
        $openingLinesQuery = \App\Models\Accounting\VoucherLine::query()
            ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
            ->where('voucher_lines.ledger_id', $ledger->id)
            ->whereDate('vouchers.voucher_date', '<', $start);

        if ($vchType) {
            $openingLinesQuery->where('vouchers.voucher_type', $vchType);
        }

        $openingLines = $openingLinesQuery->get();

        // dc values in your table are 'Dr' / 'Cr' — adjust if yours differ
        $openingDebit  = $openingLines->where('dc', 'Dr')->sum('amount');
        $openingCredit = $openingLines->where('dc', 'Cr')->sum('amount');
        $openingBalance = $openingDebit - $openingCredit; // positive = Dr balance

        // 2) Transactions within range — select voucher_date as date
        $linesQuery = \App\Models\Accounting\VoucherLine::query()
            ->select(
                'voucher_lines.id as line_id',
                'vouchers.voucher_date as date',
                'voucher_lines.line_narration as particulars',
                'voucher_lines.dc',
                'voucher_lines.amount',
                'vouchers.id as voucher_id',
                'vouchers.voucher_type as vch_type',
                'vouchers.ref_no as vch_no'   // use ref_no or vch_no if you have; your schema shows ref_no
            )
            ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
            ->where('voucher_lines.ledger_id', $ledger->id)
            ->whereBetween(DB::raw('DATE(vouchers.voucher_date)'), [$start, $end]);

        if ($vchType) {
            $linesQuery->where('vouchers.voucher_type', $vchType);
        }

        // ordering and pagination parameters (DataTables)
        $orderColumn = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $length = (int) $request->input('length', 25);
        $startOffset = (int) $request->input('start', 0);

        // map DataTables column index to DB column
        $columns = [
            0 => 'vouchers.voucher_date',
            1 => 'voucher_lines.line_narration',
            2 => 'vouchers.voucher_type',
            3 => 'vouchers.ref_no',
            4 => 'voucher_lines.amount',
            5 => 'voucher_lines.amount',
        ];

        $linesQuery->orderBy($columns[$orderColumn] ?? 'vouchers.voucher_date', $orderDir);

        $totalFiltered = $linesQuery->count();
        $rows = $linesQuery->skip($startOffset)->take($length)->get();

        // 3) Totals for the displayed period (use vouchers.voucher_date between start/end)
        $periodLinesQuery = \App\Models\Accounting\VoucherLine::query()
            ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
            ->where('voucher_lines.ledger_id', $ledger->id)
            ->whereBetween(DB::raw('DATE(vouchers.voucher_date)'), [$start, $end]);

        if ($vchType) {
            $periodLinesQuery->where('vouchers.voucher_type', $vchType);
        }

        $periodLines = $periodLinesQuery->get();
        $periodDebit  = $periodLines->where('dc', 'Dr')->sum('amount');
        $periodCredit = $periodLines->where('dc', 'Cr')->sum('amount');

        // 4) Build response rows (compute debit/credit from dc + amount)
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'date'        => $row->date,
                'particulars' => $row->particulars,
                'vch_type'    => $row->vch_type,
                'vch_no'      => $row->vch_no,
                'debit'       => $row->dc === 'Dr' ? (float) $row->amount : 0.0,
                'credit'      => $row->dc === 'Cr' ? (float) $row->amount : 0.0,
                'line_id'     => $row->line_id,
                'voucher_id'  => $row->voucher_id,
            ];
        }

        return response()->json([
            'opening' => [
                'debit'   => (float) $openingDebit,
                'credit'  => (float) $openingCredit,
                'balance' => (float) $openingBalance,
            ],
            'period' => [
                'total_debit'  => (float) $periodDebit,
                'total_credit' => (float) $periodCredit,
            ],
            'recordsTotal'    => $totalFiltered,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ]);
    }

    
    // LedgerController.php
    public function currentBalance($ledgerId)
    {
        $ledger = AccountLedger::findOrFail($ledgerId);

        // Example calculation (adjust as per your logic)
        $balance = $ledger->opening_balance ?? 0;
        // $type = $balance >= 0 ? 'Dr' : 'Cr';
        $type = $ledger->opening_type;

        return response()->json([
            'balance' => number_format($balance),
            'type'    => $type
        ]);
    }
}
