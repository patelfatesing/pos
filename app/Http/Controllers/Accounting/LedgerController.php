<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountLedger;
use App\Models\Accounting\AccountGroup;
use App\Models\Accounting\Voucher;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        } elseif ($request->type == 'purchase') {
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

    public function vouchersData(Request $request, $ledgerId)
    {
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end   = Carbon::parse($request->end_date)->endOfDay();

        $ledger = AccountLedger::findOrFail($ledgerId);

        /* ================= OPENING BALANCE ================= */
        $opening = (float) $ledger->opening_balance;
        if ($ledger->opening_type === 'Cr') {
            $opening *= -1;
        }

        $vouchers = Voucher::with(['lines.ledger'])
            ->whereBetween('voucher_date', [$start, $end])
            ->whereHas('lines', fn($q) => $q->where('ledger_id', $ledgerId))
            ->orderBy('voucher_date')
            ->orderBy('id')
            ->get();

        $rows = [];
        $totalDebit  = 0.0;
        $totalCredit = 0.0;

        foreach ($vouchers as $v) {

            // This ledger’s own line
            $self = $v->lines->firstWhere('ledger_id', $ledgerId);
            if (!$self) continue;

            // Opposite ledgers
            $others = $v->lines->where('ledger_id', '!=', $ledgerId)->values();

            /* ================= DEBIT / CREDIT ================= */
            $debit  = null;
            $credit = null;

            if ($self->dc === 'Dr') {
                $debit = (float) $self->amount;
                $totalDebit += $debit;
            } elseif ($self->dc === 'Cr') {
                $credit = (float) $self->amount;
                $totalCredit += $credit;
            }

            /* ================= MAIN ROW ================= */
            $rows[] = [
                'type'        => 'main',
                'date'        => $v->voucher_date->format('d-M-y'),
                'particulars' => $others->count() > 1
                    ? '(as per details)'
                    : ($others->first()?->ledger->name ?? ''),
                'vch_type'    => $v->voucher_type,
                'vch_no'      => (string) $v->ref_no, // or gen_id if you use it
                'debit'       => $debit,
                'credit'      => $credit,
            ];

            /* ================= DETAIL ROWS ================= */
            if ($others->count() > 1) {
                foreach ($others as $line) {
                    $rows[] = [
                        'type'        => 'detail',
                        'date'        => '',
                        'particulars' =>
                        $line->ledger->name . ' ' .
                            number_format($line->amount, 2) . ' ' . $line->dc,
                        'vch_type' => '',
                        'vch_no'   => '',
                        'debit'    => null,
                        'credit'   => null,
                    ];
                }
            }
        }

        return response()->json([
            'data' => $rows,
            'opening' => [
                'balance' => $opening
            ],
            'period' => [
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
            ]
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
