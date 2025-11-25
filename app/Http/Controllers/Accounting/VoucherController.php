<?php

// app/Http/Controllers/Accounting/VoucherController.php
namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\{Voucher, VoucherLine, AccountLedger};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
                DB::raw('COALESCE(b.name, "-") as branch_name'),
                DB::raw("ROUND(SUM(CASE WHEN l.dc='Dr' THEN l.amount ELSE 0 END),2) as dr_total"),
                DB::raw("ROUND(SUM(CASE WHEN l.dc='Cr' THEN l.amount ELSE 0 END),2) as cr_total"),
            ])
            ->groupBy('v.id', 'v.voucher_date', 'v.voucher_type', 'v.ref_no', 'v.narration', 'b.name');

        $recordsTotal = (clone $base)->count();

        if ($searchValue !== '') {
            $base->where(function ($q) use ($searchValue) {
                $q->where('v.voucher_type', 'like', "%{$searchValue}%")
                    ->orWhere('v.ref_no', 'like', "%{$searchValue}%")
                    ->orWhere('v.narration', 'like', "%{$searchValue}%")
                    ->orWhere('b.name', 'like', "%{$searchValue}%");
            });
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

        $rows = $base->orderBy($orderBy, $orderDirection)
            ->offset($start)->limit($length)->get();

        $data = $rows->map(function ($r) {
            $ok = (float)$r->dr_total === (float)$r->cr_total;
            $status = $ok
                ? '<span class="badge bg-success">Balanced</span>'
                : '<span class="badge bg-danger">Unbalanced</span>';

            $deleteUrl = route('accounting.vouchers.destroy', $r->id);

            $actions = '
              <div class="d-flex align-items-center gap-1">
                <form method="POST" action="' . e($deleteUrl) . '" class="d-inline-block frm-del">
                  ' . csrf_field() . method_field('DELETE') . '
                  <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . (int)$r->id . '">Delete</button>
                </form>
              </div>';

            return [
                'voucher_date' => $r->voucher_date ? \Illuminate\Support\Carbon::parse($r->voucher_date)->format('Y-m-d') : '-',
                'voucher_type' => e($r->voucher_type),
                'ref_no'       => e($r->ref_no ?? '-'),
                'branch'       => e($r->branch_name),
                'narration'    => e(\Illuminate\Support\Str::limit($r->narration ?? '', 60)),
                'dr_total'     => number_format((float)$r->dr_total, 2),
                'cr_total'     => number_format((float)$r->cr_total, 2),
                'status'       => $status,
                'action'       => $actions,
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


        Rule::unique('vouchers')
            ->where(
                fn($q) => $q
                    ->where('voucher_type', $r->input('voucher_type'))
                    
            );

        return view('accounting.vouchers.create', [
            'ledgers' => AccountLedger::where('is_active', 1)->orderBy('name')->get(),
            'branches' => $branches
        ]);
    }

    public function store(Request $r)
    {
        $type = $r->input('voucher_type');

        // ---------- 0) Normalize inputs BEFORE validation ----------
        // helper to treat '' as null
        $nv = function ($v) {
            return ($v === '' || $v === null) ? null : $v;
        };

        // Prefer the correct party field depending on voucher type, but also
        // gracefully accept any of these names if your Blade still posts them.
        $partyFromPR = $nv($r->input('party_ledger_id')) ?: $nv($r->input('pr_party_ledger'));
        $partyFromTR = $nv($r->input('party_ledger_id')) ?: $nv($r->input('tr_party_ledger'));

        $party = null;
        if (in_array($type, ['Payment', 'Receipt'])) {
            $party = $partyFromPR ?: $partyFromTR;
        } elseif (in_array($type, ['Sales', 'Purchase', 'DebitNote', 'CreditNote'])) {
            $party = $partyFromTR ?: $partyFromPR;
        } // Journal/Contra keep null

        // Normalize cash/bank per mode (and drop the irrelevant one)
        $mode = $nv($r->input('mode'));
        $cashLedger = $nv($r->input('cash_ledger_id')) ?: $nv($r->input('pr_cash_ledger'));
        $bankLedger = $nv($r->input('bank_ledger_id')) ?: $nv($r->input('pr_bank_ledger'));

        if ($mode === 'cash') {
            $bankLedger = null;
        } elseif (in_array($mode, ['bank', 'upi', 'card'])) {
            $cashLedger = null;
        } else {
            // unknown/blank mode → clear both so validation can catch it if required
            $cashLedger = null;
            $bankLedger = null;
        }

        // Trade totals: accept either names, and coalesce
        $subTotal   = $nv($r->input('sub_total'))   ?? $nv($r->input('tr_subtotal'));
        $discount   = $nv($r->input('discount'))    ?? $nv($r->input('tr_discount'));
        $tax        = $nv($r->input('tax'))         ?? $nv($r->input('tr_tax'));
        $grandTotal = $nv($r->input('grand_total')) ?? $nv($r->input('tr_grand'));

        // Contra fields: accept either names
        $fromLedger = $nv($r->input('from_ledger_id')) ?? $nv($r->input('ct_from'));
        $toLedger   = $nv($r->input('to_ledger_id'))   ?? $nv($r->input('ct_to'));

        // PR amount (optional helper): accept either name
        $prAmount = $nv($r->input('amount')) ?? $nv($r->input('pr_amount'));

        // Merge normalized values into the Request so validation sees the right keys
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
            // also normalize: branch_id '', ref_no '' → null
            'branch_id'       => $nv($r->input('branch_id')),
            'ref_no'          => $nv($r->input('ref_no')),
        ]);

        // ---------- 1) Validation rules ----------
        $rules = [
            'voucher_date' => ['required', 'date'],
            'voucher_type' => ['required', Rule::in(['Journal', 'Payment', 'Receipt', 'Contra', 'Sales', 'Purchase', 'DebitNote', 'CreditNote'])],
            'ref_no'       => ['nullable', 'string', 'max:50'],
            'branch_id'    => ['nullable', 'integer', 'exists:branches,id'],
            'narration'    => ['nullable', 'string', 'max:2000'],

            // lines (accounting truth)
            'lines'                 => ['required', 'array', 'min:2'],
            'lines.*.ledger_id'     => ['required', 'exists:account_ledgers,id'],
            'lines.*.dc'            => ['required', Rule::in(['Dr', 'Cr'])],
            'lines.*.amount'        => ['required', 'numeric', 'gt:0'],
            'lines.*.line_narration' => ['nullable', 'string', 'max:1000'],

            // helpers (nullable by default)
            'party_ledger_id'  => ['nullable', 'integer', 'exists:account_ledgers,id'],
            'mode'             => ['nullable', Rule::in(['cash', 'bank', 'upi', 'card'])],
            'instrument_no'    => ['nullable', 'string', 'max:50'],
            'instrument_date'  => ['nullable', 'date'],
            'cash_ledger_id'   => ['nullable', 'integer', 'exists:account_ledgers,id'],
            'bank_ledger_id'   => ['nullable', 'integer', 'exists:account_ledgers,id'],
            'from_ledger_id'   => ['nullable', 'integer', 'exists:account_ledgers,id'],
            'to_ledger_id'     => ['nullable', 'integer', 'exists:account_ledgers,id'],

            'sub_total'        => ['nullable', 'numeric', 'gte:0'],
            'discount'         => ['nullable', 'numeric', 'gte:0'],
            'tax'              => ['nullable', 'numeric', 'gte:0'],
            'grand_total'      => ['nullable', 'numeric', 'gte:0'],
        ];

        // Conditional requirements
        if (in_array($type, ['Payment', 'Receipt'])) {
            $rules['mode'][0] = 'required';
            $rules['party_ledger_id'][0] = 'required';
            if ($r->input('mode') === 'cash') {
                $rules['cash_ledger_id'][0] = 'required';
            } elseif (in_array($r->input('mode'), ['bank', 'upi', 'card'])) {
                $rules['bank_ledger_id'][0] = 'required';
            }
        } elseif ($type === 'Contra') {
            $rules['from_ledger_id'][0] = 'required';
            $rules['to_ledger_id'][0]   = 'required';
            $rules['to_ledger_id'][]    = 'different:from_ledger_id';
        } elseif (in_array($type, ['Sales', 'Purchase', 'DebitNote', 'CreditNote'])) {
            $rules['party_ledger_id'][0] = 'required';
            if ($r->filled(['sub_total']) || $r->filled(['discount']) || $r->filled(['tax'])) {
                $rules['grand_total'][0] = 'required';
            }
        }

        // Unique ref_no within (branch_id, voucher_type) when present
        if ($r->filled('ref_no')) {
            $rules['ref_no'][] = Rule::unique('vouchers')
                ->where(
                    fn($q) => $q
                        ->where('voucher_type', $r->input('voucher_type'))
                        ->where('branch_id', $r->input('branch_id'))
                );
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
            if ($line['dc'] === 'Dr') {
                $dr += (float)$line['amount'];
            } else {
                $cr += (float)$line['amount'];
            }
        }
        if (round($dr, 2) !== round($cr, 2)) {
            return back()->withErrors(['lines' => 'Total Debit and Credit must be equal.'])->withInput();
        }

        // ---------- 3) Persist ----------
        DB::transaction(function () use ($data) {
            $voucher = \App\Models\Accounting\Voucher::create([
                'voucher_date'    => $data['voucher_date'],
                'voucher_type'    => $data['voucher_type'],
                'ref_no'          => $data['ref_no'] ?? null,
                'branch_id'       => $data['branch_id'] ?? null,
                'narration'       => $data['narration'] ?? null,
                'created_by'      => Auth::id(),

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

            foreach ($data['lines'] as $line) {
                $voucher->lines()->create([
                    'ledger_id'      => $line['ledger_id'],
                    'dc'             => $line['dc'],
                    'amount'         => round((float)$line['amount'], 2),
                    'line_narration' => $line['line_narration'] ?? null,
                ]);
            }
        });

        return redirect()->route('accounting.vouchers.index')
            ->with('success', 'Voucher posted successfully.');
    }
}
