<?php

// app/Http/Controllers/Accounting/VoucherController.php
namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\{Voucher, VoucherLine, AccountLedger, AccountGroup};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Branch;

class VoucherController extends Controller
{

    public function index()
    {
        $vouchers = Voucher::with('lines')->latest('voucher_date')->paginate(20);
        $branches = \App\Models\Branch::select('name', 'id')->get();
        $types    = ['Payment', 'Receipt', 'Contra', 'Journal', 'Purchase', 'Sales', 'Credit Note', 'Debit Note']; // adjust to your set

        return view('accounting.vouchers.index', compact('vouchers', 'branches', 'types'));
    }

    public function getData(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue      = $request->input('search.value', '');
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderColumn      = $request->input("columns.$orderColumnIndex.data", 'voucher_date');
        $orderDirection   = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';

        // Base with aggregates
        $base = DB::table('vouchers as v')
            ->leftJoin('branches as b', 'b.id', '=', 'v.branch_id')
            ->leftJoin('voucher_lines as l', 'l.voucher_id', '=', 'v.id')
            ->select([
                'v.id',
                'v.voucher_date',
                'v.voucher_type',
                'v.ref_no',
                'v.narration',
                'v.admin_status',
                'v.super_admin_status',
                DB::raw('COALESCE(b.name, "-") as branch_name'),
                DB::raw("ROUND(SUM(CASE WHEN l.dc='Dr' THEN l.amount ELSE 0 END),2) as dr_total"),
                DB::raw("ROUND(SUM(CASE WHEN l.dc='Cr' THEN l.amount ELSE 0 END),2) as cr_total"),
            ])
            ->groupBy('v.id', 'v.voucher_date', 'v.voucher_type', 'v.ref_no', 'v.narration', 'b.name', 'v.admin_status', 'v.super_admin_status');

        $recordsTotal = (clone $base)->count();

        if ($searchValue !== '') {
            $base->where(function ($q) use ($searchValue) {
                $q->where('v.voucher_type', 'like', "%{$searchValue}%")
                    ->orWhere('v.ref_no', 'like', "%{$searchValue}%")
                    ->orWhere('v.narration', 'like', "%{$searchValue}%")
                    ->orWhere('b.name', 'like', "%{$searchValue}%");
            });
        }

        // Voucher Type Filter
        if ($request->filled('voucher_type')) {
            $base->where('v.voucher_type', $request->voucher_type);
        }

        if (auth()->user()->role_id == 1) {
            $base->where('super_admin_status', 'verify');
        }

        $recordsFiltered = (clone $base)->count();

        $sortable = [
            'voucher_date' => 'v.voucher_date',
            'voucher_type' => 'v.voucher_type',
            'ref_no'       => 'v.ref_no',
            'branch_name'  => 'branch_name',
            'dr_total'     => 'dr_total',
            'cr_total'     => 'cr_total',
        ];
        $orderBy = $sortable[$orderColumn] ?? 'v.voucher_date';

        $roleId = auth()->user()->role_id;

        $userId = auth()->id();

        $listAccess = getAccess($roleId, 'accounting-voucher-manage');

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
            $base->where('created_by', $userId);
        }


        if ($request->order) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'desc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'v.created_at';
            $base->orderBy($orderColumn, $orderDir);
        } else {
            $base->orderBy('v.created_at', 'desc');
        }

        // Pagination
        if ($request->length > 0) {
            $base->skip($request->start)->take($request->length);
        }

        $rows = $base->get();

        $data = $rows->map(function ($r) {
            $ok = (float)$r->dr_total === (float)$r->cr_total;
            $status = $ok
                ? '<span class="badge bg-success">Balanced</span>'
                : '<span class="badge bg-danger">Unbalanced</span>';

            $deleteUrl = route('accounting.vouchers.destroy', $r->id);
            // $ownerId = $g->created_by;  // If available
            // if (canDo($roleId, 'product-edit', $ownerId)) {
            // }
            $viewUrl = url('/accounting/vouchers/view/' . $r->id);
            $actions = '<div class="d-flex align-items-center gap-1">';

            // View button (only verified OR admin)
            if (auth()->user()->role_id !== 1) {
                
                  if ($r->super_admin_status === 'verify') {

                    $actions .= '<a href="' . e($viewUrl) . '" class="btn btn-sm btn-success mr-1">
                    Verify
                 </a>';
                } else {
                    $actions .= '<a href="' . e($viewUrl) . '" class="btn btn-sm btn-warning mr-1">
                    Unverified
                 </a>';
                }
            } else {
               $actions .= '<a href="' . e($viewUrl) . '" class="btn btn-sm btn-success mr-1">
                    Verify
                 </a>';
            }

            // if ($r->admin_status === 'verify') {

            //     $actions .= '<a href="' . e($viewUrl) . '" class="btn btn-sm btn-success mr-1">
            //         Verify
            //      </a>';
            // } else {
            //     $actions .= '<a href="' . e($viewUrl) . '" class="btn btn-sm btn-warning mr-1">
            //         Unverified
            //      </a>';
            // }

            $actions1 = '<div class="d-flex align-items-center gap-1">';

            // Delete button (ALWAYS visible)
            $actions1 .= '<form method="POST" action="' . e($deleteUrl) . '" class="d-inline-block frm-del">
                ' . csrf_field() . method_field('DELETE') . '
                <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . (int)$r->id . '">
                    Delete
                </button>
             </form>';

            $actions1 .= '</div>';
            return [
                'voucher_date' => $r->voucher_date ? \Illuminate\Support\Carbon::parse($r->voucher_date)->format('Y-m-d') : '-',
                'voucher_type' => e($r->voucher_type),
                'ref_no'       => e($r->ref_no ?? '-'),
                'branch'       => e($r->branch_name),
                'narration'    => e(\Illuminate\Support\Str::limit($r->narration ?? '', 60)),
                'dr_total'     => number_format((float)$r->dr_total, 2),
                'cr_total'     => number_format((float)$r->cr_total, 2),
                'status'       => $status,
                'admin_status' => $actions,
                'action'       => $actions1,
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
        $branches = \App\Models\Branch::select('name', 'id')->get();


        $lastVoucher = Voucher::where('voucher_type', 'Journal')
            // ->where('branch_id', $r->branch_id) // optional
            ->orderBy('id', 'desc')
            ->value('ref_no');

        if ($lastVoucher !== null) {
            $num = (int) str_replace('JN-', '', $lastVoucher);
            $num++;
            $lastVoucher = 'JN-' . str_pad($num, 4, '0', STR_PAD_LEFT);
        } else {
            $lastVoucher = "JN-0001";
        }


        return view('accounting.vouchers.create', [
            'ledgers' => AccountLedger::where('is_active', 1)->orderBy('name')->get(),
            'branches' => $branches,
            'lastVoucher' => $lastVoucher,
        ]);
    }

    public function store(Request $r)
    {

        $type = $r->input('voucher_type');

        $nv = fn($v) => ($v === '' || $v === null) ? null : $v;

        $partyFromPR = $nv($r->input('party_ledger_id')) ?: $nv($r->input('pr_party_ledger'));
        $partyFromTR = $nv($r->input('party_ledger_id')) ?: $nv($r->input('tr_party_ledger'));

        $party = null;
        if (in_array($type, ['Payment', 'Receipt'])) {
            $party = $partyFromPR ?: $partyFromTR;
        } elseif (in_array($type, ['Sales', 'Purchase', 'DebitNote', 'CreditNote'])) {
            $party = $partyFromTR ?: $partyFromPR;
        }

        $mode = $nv($r->input('mode'));
        $cashLedger = $nv($r->input('cash_ledger_id')) ?: $nv($r->input('pr_cash_ledger'));
        $bankLedger = $nv($r->input('bank_ledger_id')) ?: $nv($r->input('pr_bank_ledger'));

        if ($mode === 'cash') {
            $bankLedger = null;
        } elseif (in_array($mode, ['bank', 'upi', 'card'])) {
            $cashLedger = null;
        } else {
            $cashLedger = null;
            $bankLedger = null;
        }

        $subTotal   = $nv($r->input('sub_total'))   ?? $nv($r->input('tr_subtotal'));
        $discount   = $nv($r->input('discount'))    ?? $nv($r->input('tr_discount'));
        $tax        = $nv($r->input('tax'))         ?? $nv($r->input('tr_tax'));
        $grandTotal = $nv($r->input('grand_total')) ?? $nv($r->input('tr_grand'));

        $fromLedger = $nv($r->input('from_ledger_id')) ?? $nv($r->input('ct_from'));
        $toLedger   = $nv($r->input('to_ledger_id'))   ?? $nv($r->input('ct_to'));


        $r->merge([
            'party_ledger_id' => $party,
            'mode'            => $mode,
            'cash_ledger_id'  => $cashLedger,
            'bank_ledger_id'  => $bankLedger,
            'sub_total'       => $subTotal,
            'discount'        => $discount,
            'tax'             => $tax,
            'grand_total'     => $grandTotal,
            'from_ledger_id'  => $fromLedger,
            'to_ledger_id'    => $toLedger,
            'branch_id'       => $nv($r->input('branch_id')),
            'ref_no'          => $nv($r->input('ref_no')),
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. REMOVE EMPTY ROWS (IMPORTANT)
        |--------------------------------------------------------------------------
        */
        $lines = collect($r->input('lines', []))
            ->filter(
                fn($l) =>
                !empty($l['ledger_id']) &&
                    !empty($l['dc']) &&
                    floatval($l['amount'] ?? 0) > 0
            )
            ->values()
            ->toArray();

        $r->merge(['lines' => $lines]);

        /*
        |--------------------------------------------------------------------------
        | 3. VALIDATION
        |--------------------------------------------------------------------------
        */
        $rules = [
            'voucher_date' => ['required', 'date'],
            'voucher_type' => ['required', Rule::in([
                'Journal',
                'Payment',
                'Receipt',
                'Contra',
                'Sales',
                'Purchase',
                'DebitNote',
                'CreditNote'
            ])],
            'ref_no'       => ['nullable', 'string', 'max:50'],
            'branch_id'    => ['nullable', 'integer', 'exists:branches,id'],
            'narration'    => ['nullable', 'string', 'max:2000'],

            'lines'             => ['required', 'array', 'min:2'],
            'lines.*.ledger_id' => ['required', 'exists:account_ledgers,id'],
            'lines.*.dc'        => ['required', Rule::in(['Dr', 'Cr'])],
            'lines.*.amount'    => ['required', 'numeric', 'gt:0'],
        ];

        if ($r->filled('ref_no')) {
            $rules['ref_no'][] = Rule::unique('vouchers')->where(
                fn($q) => $q->where('voucher_type', $r->voucher_type)
            );
        }

        $data = $r->validate($rules);

        /*
            |--------------------------------------------------------------------------
            | 4. DR / CR BALANCE CHECK
            |--------------------------------------------------------------------------
            */
        $dr = 0;
        $cr = 0;

        foreach ($data['lines'] as $line) {
            if ($line['dc'] === 'Dr') $dr += $line['amount'];
            if ($line['dc'] === 'Cr') $cr += $line['amount'];
        }

        if (round($dr, 2) !== round($cr, 2)) {
            return back()
                ->withErrors(['lines' => 'Debit and Credit totals must be equal.'])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 5. SAVE DATA
        |--------------------------------------------------------------------------
        */

        $voucherVerify = 'unverify';
        if (auth()->id() == 1) {
            $voucherVerify = 'verify';
        }

        DB::transaction(function () use ($data) {

            $voucher = \App\Models\Accounting\Voucher::create([
                'voucher_date' => $data['voucher_date'],
                'voucher_type' => $data['voucher_type'],
                'ref_no'       => $data['ref_no'] ?? null,
                'branch_id'    => $data['branch_id'] ?? null,
                'narration'    => $data['narration'] ?? null,
                'created_by'   => auth()->id(),

                'party_ledger_id' => $data['party_ledger_id'] ?? null,
                'mode'            => $data['mode'] ?? null,
                'cash_ledger_id'  => $data['cash_ledger_id'] ?? null,
                'bank_ledger_id'  => $data['bank_ledger_id'] ?? null,
                'from_ledger_id'  => $data['from_ledger_id'] ?? null,
                'to_ledger_id'    => $data['to_ledger_id'] ?? null,

                'sub_total'   => $data['sub_total'] ?? 0,
                'discount'    => $data['discount'] ?? 0,
                'tax'         => $data['tax'] ?? 0,
                'grand_total' => $data['grand_total'] ?? 0,
                'admin_status' => auth()->user()->hasRole('admin') ? 'verify' : 'unverify'
            ]);

            foreach ($data['lines'] as $line) {


                $voucher->lines()->create([
                    'ledger_id'      => $line['ledger_id'],
                    'dc'             => $line['dc'],
                    'amount'         => round($line['amount'], 2),
                    'line_narration' => $line['line_narration'] ?? null,
                ]);


                $ledger = AccountLedger::lockForUpdate()->findOrFail($line['ledger_id']);

                // Calculate updated balance
                $result = $this->calculateLedgerBalance(
                    $ledger,
                    $line['dc'],
                    $line['amount']
                );

                // Update ledger balance
                // $ledger->update([
                //     'opening_balance' => $result['balance'],
                //     'opening_type'    => $result['type'],
                // ]);
            }
        });

        return redirect()
            ->route('accounting.vouchers.index')
            ->with('success', 'Accounting Voucher created successfully.');
    }

    private function calculateLedgerBalance(AccountLedger $ledger, string $dc, float $amount)
    {
        // Convert opening balance to signed number
        // $current = $ledger->opening_type === 'Dr'
        //     ? $ledger->opening_balance
        //     : -$ledger->opening_balance;
        $current = $ledger->opening_balance;


        // Apply transaction
        if ($dc === 'Dr') {
            $current += $amount;
        } else {
            $current -= $amount;
        }

        return [
            'balance' => $current >= 0 ? $current : -$current,
            'type'    => $dc,
        ];
    }

    public function edit($id)
    {
        $voucher = Voucher::with('lines')->findOrFail($id);

        return view('accounting.vouchers.edit', [
            'voucher' => $voucher,
            'ledgers' => AccountLedger::all(),
        ]);
    }

    public function getLastRef(Request $request)
    {
        $voucherType = $request->voucher_type;
        // $branchId    = $request->branch_id;

        $voucherType = $request->voucher_type;

        // Prefix
        $prefixMap = [
            'Journal'  => 'JN',
            'Payment'  => 'PM',
            'Receipt'  => 'RC',
            'Contra'   => 'CT',
            'Purchase' => 'PU',
        ];

        $prefix = $prefixMap[$voucherType] ?? 'VN';

        // Get last voucher ref_no
        $lastVoucher = Voucher::where('voucher_type', $voucherType)
            // ->where('branch_id', $request->branch_id) // enable if needed
            ->orderBy('id', 'desc')
            ->value('ref_no');

        if ($lastVoucher) {
            // Remove prefix safely (JN-, PM-, etc.)
            $num = (int) preg_replace('/[^0-9]/', '', $lastVoucher);
            $num++;
        } else {
            $num = 1;
        }

        // Generate next voucher no
        $nextRefNo = $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'next_ref_no' => $nextRefNo
        ]);
    }

    public function edit1($id)
    {
        // load voucher with lines and ledger relation
        $voucher = Voucher::with(['lines.ledger'])->findOrFail($id);

        // dropdown lists (same as create)
        $branches = Branch::select('id', 'name')->orderBy('name')->get();
        $ledgers  = AccountLedger::where('is_active', 1)->orderBy('name')->get();

        // optional: groups if any ledger partial expects this
        $groups = AccountGroup::orderBy('name')->get();

        // If your voucher has a "party_ledger_id" (or similar) use it as $ledger for partials
        $ledger = null;
        if (!empty($voucher->party_ledger_id)) {
            $ledger = AccountLedger::find($voucher->party_ledger_id);
        }

        // JS-friendly maps used by your create JS (adjust group ids if yours differ)
        $voucherGroupMap = [
            'Journal'    => [],
            'Payment'    => [17, 18, 20, 21, 13, 14],
            'Receipt'    => [17, 18, 19, 10, 11],
            'Contra'     => [17, 18],
            'Sales'      => [19, 9, 21],
            'Purchase'   => [12, 21, 20],
            'DebitNote'  => [20, 12, 21],
            'CreditNote' => [19, 9, 21],
        ];

        $dcMap = [
            'Journal' => ['Cr', 'Dr'],
            'Contra'  => ['Cr', 'Dr'],
            'Receipt' => ['Cr', 'Dr'],
            'default' => ['Dr', 'Cr'],
        ];

        return view('accounting.vouchers.edit', [
            'voucher'  => $voucher,
            'ledgers'  => $ledgers,
            'branches' => $branches,
            'groups'   => $groups,
            'ledger'   => $ledger,              // <<< prevents "Undefined variable $ledger"
            'VOUCHER_GROUP_MAP' => $voucherGroupMap,
            'DC_MAP'            => $dcMap,
        ]);
    }

    public function update(Request $r)
    {
        $id = $r->input('id');
        $voucher = Voucher::with('lines')->findOrFail($id);

        // ---------- 0) Normalize inputs (same logic as store) ----------
        $nv = function ($v) {
            return ($v === '' || $v === null) ? null : $v;
        };

        $type = $r->input('voucher_type');

        $partyFromPR = $nv($r->input('party_ledger_id')) ?: $nv($r->input('pr_party_ledger'));
        $partyFromTR = $nv($r->input('party_ledger_id')) ?: $nv($r->input('tr_party_ledger'));

        $party = null;
        if (in_array($type, ['Payment', 'Receipt'])) {
            $party = $partyFromPR ?: $partyFromTR;
        } elseif (in_array($type, ['Sales', 'Purchase', 'DebitNote', 'CreditNote'])) {
            $party = $partyFromTR ?: $partyFromPR;
        }

        $mode = $nv($r->input('mode'));
        $cashLedger = $nv($r->input('cash_ledger_id')) ?: $nv($r->input('pr_cash_ledger'));
        $bankLedger = $nv($r->input('bank_ledger_id')) ?: $nv($r->input('pr_bank_ledger'));

        if ($mode === 'cash') {
            $bankLedger = null;
        } elseif (in_array($mode, ['bank', 'upi', 'card'])) {
            $cashLedger = null;
        } else {
            $cashLedger = null;
            $bankLedger = null;
        }

        $subTotal   = $nv($r->input('sub_total'))   ?? $nv($r->input('tr_subtotal'));
        $discount   = $nv($r->input('discount'))    ?? $nv($r->input('tr_discount'));
        $tax        = $nv($r->input('tax'))         ?? $nv($r->input('tr_tax'));
        $grandTotal = $nv($r->input('grand_total')) ?? $nv($r->input('tr_grand'));

        $fromLedger = $nv($r->input('from_ledger_id')) ?? $nv($r->input('ct_from'));
        $toLedger   = $nv($r->input('to_ledger_id'))   ?? $nv($r->input('ct_to'));

        $prAmount = $nv($r->input('amount')) ?? $nv($r->input('pr_amount'));

        $r->merge([
            'party_ledger_id' => $party,
            'mode'            => $mode,
            'cash_ledger_id'  => $cashLedger,
            'bank_ledger_id'  => $bankLedger,
            'sub_total'       => $subTotal,
            'discount'        => $discount,
            'tax'             => $tax,
            'grand_total'     => $grandTotal,
            'from_ledger_id'  => $fromLedger,
            'to_ledger_id'    => $toLedger,
            'amount'          => $prAmount,
            'branch_id'       => $nv($r->input('branch_id')),
            'ref_no'          => $nv($r->input('ref_no')),
        ]);

        // ---------- 1) Validation (same as store, but ref_no unique ignores current voucher) ----------
        $rules = [
            'voucher_date' => ['required', 'date'],
            'voucher_type' => ['required', Rule::in(['Journal', 'Payment', 'Receipt', 'Contra', 'Sales', 'Purchase', 'DebitNote', 'CreditNote'])],
            'ref_no'       => ['nullable', 'string', 'max:50'],
            'branch_id'    => ['nullable', 'integer', 'exists:branches,id'],
            'narration'    => ['nullable', 'string', 'max:2000'],

            'lines'                 => ['required', 'array', 'min:2'],
            'lines.*.ledger_id'     => ['required', 'exists:account_ledgers,id'],
            'lines.*.dc'            => ['required', Rule::in(['Dr', 'Cr'])],
            'lines.*.amount'        => ['required', 'numeric', 'gt:0'],
            'lines.*.line_narration' => ['nullable', 'string', 'max:1000'],
        ];

        // unique ref_no within (branch_id, voucher_type) when present — ignore current voucher id
        if ($r->filled('ref_no')) {
            $rules['ref_no'][] = Rule::unique('vouchers')
                ->where(
                    fn($q) => $q
                        ->where('voucher_type', $r->input('voucher_type'))
                        ->where('branch_id', $r->input('branch_id'))
                )->ignore($voucher->id);
        }

        $data = $r->validate($rules);

        // Compute grand_total if header totals provided but grand_total missing
        if (!$r->filled('grand_total') && ($r->filled('sub_total') || $r->filled('discount') || $r->filled('tax'))) {
            $data['grand_total'] = round(
                (float)($r->input('sub_total', 0)) - (float)($r->input('discount', 0)) + (float)($r->input('tax', 0)),
                2
            );
        }

        // ---------- 2) Check Dr/Cr balance ----------
        $dr = 0;
        $cr = 0;
        foreach ($data['lines'] as $line) {
            if ($line['dc'] === 'Dr') $dr += (float)$line['amount'];
            else $cr += (float)$line['amount'];
        }
        if (round($dr, 2) !== round($cr, 2)) {
            return back()->withErrors(['lines' => 'Total Debit and Credit must be equal.'])->withInput();
        }

        // ---------- 3) Persist (transaction) ----------
        DB::transaction(function () use ($voucher, $data) {
            // update voucher header
            $voucher->update([
                'voucher_date'    => $data['voucher_date'],
                'voucher_type'    => $data['voucher_type'],
                'ref_no'          => $data['ref_no'] ?? null,
                'branch_id'       => $data['branch_id'] ?? null,
                'narration'       => $data['narration'] ?? null,
                'updated_by'      => Auth::id(),

                'party_ledger_id' => $data['party_ledger_id'] ?? null,
                'mode'            => $data['mode'] ?? null,
                'instrument_no'   => $data['instrument_no'] ?? null,
                'instrument_date' => $data['instrument_date'] ?? null,
                'cash_ledger_id'  => $data['cash_ledger_id'] ?? null,
                'bank_ledger_id'  => $data['bank_ledger_id'] ?? null,
                'from_ledger_id'  => $data['from_ledger_id'] ?? null,
                'to_ledger_id'    => $data['to_ledger_id'] ?? null,

                'sub_total'       => $data['sub_total']   ?? 0,
                'discount'        => $data['discount']    ?? 0,
                'tax'             => $data['tax']         ?? 0,
                'grand_total'     => $data['grand_total'] ?? 0,
            ]);

            // delete old lines and recreate (simple and safe)
            $voucher->lines()->delete();

            foreach ($data['lines'] as $line) {
                $voucher->lines()->create([
                    'ledger_id'      => $line['ledger_id'],
                    'dc'             => $line['dc'],
                    'amount'         => round((float)$line['amount'], 2),
                    'line_narration' => $line['line_narration'] ?? null,
                ]);
            }
        });

        return redirect()->route('accounting.vouchers.index')->with('success', 'Voucher updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $voucher = Voucher::with('lines')->findOrFail($id);

        DB::transaction(function () use ($voucher) {

            // Delete all voucher lines
            foreach ($voucher->lines as $line) {
                $line->delete();   // hard delete
            }

            // Delete voucher
            $voucher->delete();    // hard delete
        });

        return response()->json([
            'success' => true,
            'message' => 'Voucher and all lines deleted successfully.',
        ]);
    }

    public function destroyVoucher($id)
    {
        $voucher = Voucher::with('lines')->findOrFail($id);

        DB::transaction(function () use ($voucher) {
            foreach ($voucher->lines as $line) {
                $line->delete();
            }
            $voucher->delete();
        });

        return response()->json(['success' => true, 'message' => 'Voucher deleted permanently.']);
    }

    public function voucherView($id)
    {
        // Get voucher with relations (adjust as per your models)
        $voucher = Voucher::with([
            'branch',
            'partyLedger',
            'cashLedger',
            'bankLedger',
            'fromLedger',
            'toLedger',
            'lines.ledger' // ✅ correct relation
        ])->findOrFail($id);

        // dd($voucher);

        return view('accounting.vouchers.view_voucher', compact('voucher'));
    }

    public function vouchersData($voucherId)
    {
        $voucher = Voucher::with(['lines.ledger'])->findOrFail($voucherId);

        $rows = [];
        $totalDebit  = 0;
        $totalCredit = 0;

        foreach ($voucher->lines as $line) {

            $debit  = 0;
            $credit = 0;

            if ($line->dc === 'Dr') {
                $debit = (float) $line->amount;
                $totalDebit += $debit;
            } elseif ($line->dc === 'Cr') {
                $credit = (float) $line->amount;
                $totalCredit += $credit;
            }

            $rows[] = [
                'type'        => 'main',
                'date'        => optional($voucher->voucher_date)->format('d-M-y'),
                'particulars' => $line->ledger->name ?? '',
                'vch_type'    => $voucher->voucher_type,
                'vch_no'      => $voucher->ref_no,
                'debit'       => $debit,
                'credit'      => $credit,
                'edit_url'    => route('accounting.vouchers.edit', $voucher->id),
            ];
        }

        return response()->json([
            'data' => $rows,

            // no opening in voucher view → keep 0
            'opening' => [
                'balance' => 0
            ],

            'period' => [
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
            ]
        ]);
    }

    public function destroyVoucherLine($id)
    {
        $line = \App\Models\Accounting\VoucherLine::findOrFail($id);
        $line->delete();
        return response()->json(['success' => true, 'message' => 'Voucher line deleted permanently.']);
    }

    public function updateParticular(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $voucher = Voucher::findOrFail($id);

            // ❌ Prevent update if verified (recommended)
            if ($voucher->admin_status === 'verify') {
                return back()->with('error', 'Verified voucher cannot be edited');
            }

            /* ================= UPDATE VOUCHER ================= */
            $voucher->update([
                'voucher_date' => $request->voucher_date,
                'voucher_type' => $request->voucher_type,
                'ref_no'       => $request->ref_no,
                'narration'    => $request->narration,
                'mode'         => $request->mode,
            ]);

            /* ================= DELETE OLD LINES ================= */
            $voucher->lines()->delete();

            $totalDebit  = 0;
            $totalCredit = 0;

            /* ================= INSERT NEW LINES ================= */
            foreach ($request->lines as $line) {

                if (empty($line['ledger_id']) || empty($line['amount'])) {
                    continue;
                }

                $dc = $line['dc']; // Dr / Cr
                $amount = (float) $line['amount'];

                if ($dc === 'Dr') {
                    $totalDebit += $amount;
                } else {
                    $totalCredit += $amount;
                }

                $voucher->lines()->create([
                    'ledger_id' => $line['ledger_id'],
                    'dc'        => $dc,
                    'amount'    => $amount,
                ]);
            }

            /* ================= VALIDATION ================= */
            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                DB::rollBack();
                return back()->with('error', 'Debit and Credit must be equal');
            }

            /* ================= UPDATE TOTAL ================= */
            $voucher->update([
                'grand_total' => $totalDebit
            ]);

            DB::commit();

            return redirect()->route('accounting.vouchers.index')
                ->with('success', 'Voucher updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        $voucher = Voucher::findOrFail($request->id);

        $voucher->admin_status = $request->status;
         $voucher->super_admin_status = $request->status;
        $voucher->save();

        return response()->json(['success' => true]);
    }

    // public function edit(Voucher $voucher) // or: public function edit($id)
    // {
    //     // If not using route-model-binding, do:
    //     // $voucher = Voucher::with(['lines.ledger'])->findOrFail($id);

    //     // load branches and ledgers for dropdowns (same as create)
    //     $branches = Branch::select('id', 'name')->orderBy('name')->get();
    //     $ledgers  = AccountLedger::where('is_active', 1)->orderBy('name')->get();

    //     // eager-load voucher lines and their ledger relation to pre-populate form
    //     $voucher->load(['lines.ledger']);

    //     // Pass any other helper data your view expects (example: account group map)
    //     // $voucherGroupMap = config('accounting.voucher_group_map', []);

    //     return view('accounting.vouchers.edit', [
    //         'voucher'  => $voucher,
    //         'ledgers'  => $ledgers,
    //         'branches' => $branches,
    //         // 'voucherGroupMap' => $voucherGroupMap,
    //     ]);
    // }
}
