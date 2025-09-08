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
        return view('accounting.vouchers.create', [
            'ledgers' => AccountLedger::where('is_active', 1)->orderBy('name')->get(),
            'branches' => $branches
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'voucher_date' => ['required', 'date'],
            'voucher_type' => ['required', Rule::in(['Journal', 'Payment', 'Receipt', 'Contra', 'Sales', 'Purchase', 'DebitNote', 'CreditNote'])],
            'ref_no'       => ['nullable', 'string', 'max:50'],
            'branch_id'    => ['nullable', 'integer'],
            'narration'    => ['nullable', 'string', 'max:2000'],
            'lines'        => ['required', 'array', 'min:2'],
            'lines.*.ledger_id' => ['required', 'exists:account_ledgers,id'],
            'lines.*.dc'        => ['required', Rule::in(['Dr', 'Cr'])],
            'lines.*.amount'    => ['required', 'numeric', 'gt:0'],
            'lines.*.line_narration' => ['nullable', 'string', 'max:1000'],
        ]);

        $dr = 0;
        $cr = 0;
        foreach ($data['lines'] as $line) {
            $line['dc'] === 'Dr' ? $dr += $line['amount'] : $cr += $line['amount'];
        }
        if (round($dr, 2) !== round($cr, 2)) {
            return back()->withErrors(['lines' => 'Total Debit and Credit must be equal.'])->withInput();
        }

        DB::transaction(function () use ($data) {
            $voucher = Voucher::create([
                'voucher_date' => $data['voucher_date'],
                'voucher_type' => $data['voucher_type'],
                'ref_no' => $data['ref_no'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'narration' => $data['narration'] ?? null,
                'created_by' => Auth::id(),
            ]);
            foreach ($data['lines'] as $line) {
                $voucher->lines()->create($line);
            }
        });

        return redirect()->route('accounting.vouchers.index')
            ->with('success', 'Voucher posted successfully.');
    }
}
