<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ReportTallyController extends Controller
{

    public function profitLoss(Request $request)
    {
        $branches = DB::table('branches')
            ->select('id', 'name')
            ->when(DB::getSchemaBuilder()->hasColumn('branches', 'is_deleted'), fn($q) => $q->where('is_deleted', 'no'))
            ->when(DB::getSchemaBuilder()->hasColumn('branches', 'is_active'),  fn($q) => $q->where('is_active', 'yes'))
            ->orderBy('name')->get();

        return view('reports_tally.pnl_tally', compact('branches'));
    }

    public function getProfitLossData(Request $request)
    {
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = \Carbon\Carbon::now($tz);

        $start = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();

        $end = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        if (auth()->user()->role_id === 1) {
            $verify = $request->input('super_admin_status', 'verify');
        }

        $verify = $request->input('admin_status', 'verify');


        /* ================= BASE QUERY ================= */
        $linesBase = DB::table('voucher_lines as vl')
            ->join('vouchers as v', 'v.id', '=', 'vl.voucher_id')
            ->join('account_ledgers as l', 'l.id', '=', 'vl.ledger_id')
            ->join('account_groups as g', 'g.id', '=', 'l.group_id')
            ->where('v.admin_status', $verify)
            ->whereBetween('v.voucher_date', [$start->toDateString(), $end->toDateString()])
            ->where('l.is_deleted', 0);

        /* ================= SALES ================= */
        $salesAccounts = (float) (clone $linesBase)
            ->where('g.nature', 'Income')
            ->where('g.affects_gross', 1)
            ->selectRaw("
            COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0) -
            COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0)
        ")
            ->value('amt');

        /* ================= SALES GROUP ================= */
       $salesChildren = DB::table('vouchers as v')
            ->join('invoices as i', 'i.id', '=', 'v.gen_id')
            ->where('v.admin_status', $verify)
            ->whereBetween('v.voucher_date', [$start->toDateString(), $end->toDateString()])
            ->where('v.voucher_type', 'Sales')
            ->select('i.items')
            ->get()
            ->flatMap(function ($row) {

                $items = json_decode($row->items, true);

                if (!is_array($items)) return [];

                return collect($items)->map(function ($item) {
                    return [
                        'subcategory' => $item['subcategory'] ?? 'Others',
                        'amount' => (float)($item['price'] ?? 0)
                    ];
                });
            })
            ->groupBy('subcategory')
            ->map(function ($rows, $subcategory) {

                $total = $rows->sum('amount');

                return [
                    'label' => $subcategory, // ✅ category name
                    'amount' => number_format($total, 2)
                ];
            })
            ->values()
            ->all();
        /* ================= COMMON FUNCTION ================= */
        $groupWithLedger = function ($scope) use ($linesBase) {

            $base = $scope(clone $linesBase);

            $groups = (clone $base)
                ->selectRaw('g.id as gid, g.name as gname')
                ->groupBy('g.id', 'g.name')
                ->get();

            $total = 0;
            $children = [];

            foreach ($groups as $g) {

                $ledgers = (clone $base)
                    ->where('g.id', $g->gid)
                    ->selectRaw("
                    l.id as lid,
                    l.name as lname,
                    COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0) as amt
                ")
                    ->groupBy('l.id', 'l.name')
                    ->havingRaw('amt <> 0')
                    ->get();

                $groupTotal = 0;
                $rows = [];

                foreach ($ledgers as $r) {
                    $amt = (float)$r->amt;
                    $groupTotal += $amt;

                    $rows[] = [
                        'label' => $r->lname,
                        'amount' => number_format($amt, 2),
                        'ledger_id' => $r->lid
                    ];
                }

                if ($groupTotal == 0) continue;

                $total += $groupTotal;

                $children[] = [
                    'label' => $g->gname,
                    'amount' => number_format($groupTotal, 2),
                    'group_id' => $g->gid,
                    'children' => $rows
                ];
            }

            return ['total' => $total, 'children' => $children];
        };

        /* ================= SECTIONS ================= */
        $purchase = $groupWithLedger(fn($q) => $q->whereRaw("LOWER(g.name) like '%purchase%'"));

        $directRaw = $groupWithLedger(
                fn($q) => $q->where('g.nature', 'Expense')->where('g.affects_gross', 1)
            );

            // ✅ FINAL DUPLICATE FIX (flatten)
            $direct = [
                'total' => $directRaw['total'],
                'children' => collect($directRaw['children'])->flatMap(function ($item) {

                    // अगर group है → उसके अंदर के ledger निकालो
                    if (isset($item['children'])) {
                        return $item['children'];
                    }

                    return [$item];
                })->values()->all()
            ];

            $indirectRaw = $groupWithLedger(
                fn($q) => $q->where('g.nature', 'Expense')
                    ->where(fn($w) => $w->where('g.affects_gross', 0)->orWhereNull('g.affects_gross'))
            );

            $indirect = [
                'total' => $indirectRaw['total'],
                'children' => collect($indirectRaw['children'])->flatMap(fn($i) =>
                    isset($i['children']) ? $i['children'] : [$i]
                )->values()->all()
            ];
        /* ================= STOCK ================= */
        $valueStock = function ($date, $isOpening = false) {

            $rows = DB::table('daily_product_stocks as dps')
                ->whereDate('dps.date', '<=', $date->toDateString())
                ->orderByDesc('dps.date')
                ->get()
                ->groupBy('product_id')
                ->map(function ($items) use ($isOpening) {
                    $row = $items->first();
                    return $isOpening
                        ? (float) $row->opening_stock
                        : (float) $row->closing_stock;
                });

            if ($rows->isEmpty()) return 0;

            $productIds = $rows->keys()->toArray();

            $prices = DB::table('products')
                ->whereIn('id', $productIds)
                ->pluck('cost_price', 'id');

            $total = 0;

            foreach ($rows as $pid => $qty) {
                $total += $qty * ($prices[$pid] ?? 0);
            }

            return $total;
        };

        $openingStock = $valueStock($start, true);
        $closingStock = $valueStock($end, false);

        /* ================= CALC ================= */
        $tradingDr = $openingStock + $purchase['total'] + $direct['total'];
        $tradingCr = $salesAccounts + $closingStock;

        $grossProfit = max(0, $tradingCr - $tradingDr);
        $grossLoss   = max(0, $tradingDr - $tradingCr);

        $plDrBase = $indirect['total'] + $grossLoss;
        $plCrBase = $grossProfit;

        $nettProfit = max(0, $plCrBase - $plDrBase);
        $nettLoss   = max(0, $plDrBase - $plCrBase);

        /* ================= RESPONSE ================= */
        return response()->json([

            'trading' => [
                'dr' => [
                    'rows' => [
                        ['label' => 'Opening Stock', 'amount' => number_format($openingStock, 2)],

                        [
                            'label' => 'Purchase Accounts',
                            'amount' => number_format($purchase['total'], 2),
                            'children' => $purchase['children'],
                            'section' => 'purchase',
                            'section_group_id' => $purchase['children'][0]['group_id'] ?? null
                        ],

                        [
                            'label' => 'Direct Expenses',
                            'amount' => number_format($direct['total'], 2),
                            'children' => $direct['children'],
                            'section' => 'direct',
                            'section_group_id' => $direct['children'][0]['group_id'] ?? null
                        ],

                        ['label' => 'Gross Profit c/o', 'amount' => number_format($grossProfit, 2)],
                    ]
                ],

                'cr' => [
                    'rows' => [
                        [
                    'label' => 'Sales Accounts',
                    'amount' => number_format($salesAccounts, 2),
                    'children' => $salesChildren,
                    'section' => 'sales',
                    'section_group_id' => $salesChildren[0]['group_id'] ?? null // ✅ FIX
                ],

                        ['label' => 'Closing Stock', 'amount' => number_format($closingStock, 2)],
                        ['label' => 'Gross Loss c/o', 'amount' => number_format($grossLoss, 2)],
                    ]
                ],

                'table_total' => number_format(max($tradingDr, $tradingCr), 2)
            ],

            'pl' => [
                'dr' => [
                    'rows' => [
                        ['label' => 'Gross Loss b/f', 'amount' => number_format($grossLoss, 2)],

                        [
                            'label' => 'Indirect Expenses',
                            'amount' => number_format($indirect['total'], 2),
                            'children' => $indirect['children'],
                            'section' => 'indirect',
                            'section_group_id' => $indirect['children'][0]['group_id'] ?? null
                        ],

                        ['label' => 'Nett Profit', 'amount' => number_format($nettProfit, 2)],
                    ]
                ],

                'cr' => [
                    'rows' => [
                        ['label' => 'Gross Profit b/f', 'amount' => number_format($grossProfit, 2)],
                        ['label' => 'Nett Loss', 'amount' => number_format($nettLoss, 2)],
                    ]
                ],

                'table_total' => number_format(max($plDrBase, $plCrBase), 2)
            ]
        ]);
    }

    public function profitLossPdf(Request $request)
    {
        // 1) Reuse the JSON method (no data-code changes)
        $json = $this->getProfitLossData($request);           // Illuminate\Http\JsonResponse
        $data = $json->getData(true);                         // decode to array

        // 2) Optional: create a nice filename
        $period    = $data['header']['period'] ?? now()->toDateString();
        $branch    = $data['header']['branch'] ?? 'All-Branches';
        $safeName  = preg_replace('/[^A-Za-z0-9\- ]/', '', $branch);
        $fileName  = "Profit-and-Loss_{$safeName}_" . str_replace([' to ', ' '], ['_', ''], $period) . ".pdf";

        // 3) Render Blade to PDF
        $pdf = app('dompdf.wrapper');
        $pdf->setPaper('A4', 'portrait'); // change to 'landscape' if you prefer
        $pdf->loadView('reports.profit_loss_pdf', [
            'payload' => $data,
        ])->setOptions([
            'isRemoteEnabled' => true,
            'dpi'             => 144,
        ]);

        // 4) Stream (or ->download($fileName))
        return $pdf->stream($fileName);
    }

    public function balanceSheet(Request $request)
    {
        // For the branch filter dropdown
        $branches = DB::table('branches')->select('id', 'name')->orderBy('name')->get();
        return view('reports_tally.balance_sheet', compact('branches'));
    }

    public function getBalanceSheetData(Request $request)
    {
        $tz   = config('app.timezone', 'Asia/Kolkata');
        $now  = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->start_date, $tz)->startOfDay()
            : $now->copy()->firstOfMonth()->startOfDay();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->end_date, $tz)->endOfDay()
            : $now->copy()->endOfDay();

        $vDateCol = 'created_at';

        // ===============================
        // Ledger Closing Balance
        // ===============================
        $rows = DB::table('voucher_lines as vl')
            ->join('vouchers as v', 'v.id', '=', 'vl.voucher_id')
            ->join('account_ledgers as l', 'l.id', '=', 'vl.ledger_id')
            ->join('account_groups as g', 'g.id', '=', 'l.group_id')
            ->whereDate("v.$vDateCol", '<=', $end->toDateString())
            ->selectRaw('
            l.id as ledger_id,
            l.name as ledger_name,
            g.id as group_id,
            g.name as group_name,
            g.nature,
            SUM(CASE WHEN vl.dc="Dr" THEN vl.amount ELSE 0 END) as dr,
            SUM(CASE WHEN vl.dc="Cr" THEN vl.amount ELSE 0 END) as cr
        ')
            ->groupBy('l.id', 'l.name', 'g.id', 'g.name', 'g.nature')
            ->get();

        // ===============================
        // Build Group Totals
        // ===============================
        $assets = [];
        $liabilities = [];

        foreach ($rows as $r) {

            $net = (float)$r->dr - (float)$r->cr;

            if (abs($net) < 0.0001) continue;

            // ===============================
            // ASSETS
            // ===============================
            if ($net > 0) {

                if (!isset($assets[$r->group_id])) {
                    $assets[$r->group_id] = [
                        'id' => $r->group_id,
                        'label' => $r->group_name,
                        'total' => 0
                    ];
                }

                $assets[$r->group_id]['total'] += $net;
            }

            // ===============================
            // LIABILITIES
            // ===============================
            else {

                if (!isset($liabilities[$r->group_id])) {
                    $liabilities[$r->group_id] = [
                        'id' => $r->group_id,
                        'label' => $r->group_name,
                        'total' => 0
                    ];
                }

                $liabilities[$r->group_id]['total'] += abs($net);
            }
        }

        // ===============================
        // Convert to Rows
        // ===============================
        $assetRows = [];
        foreach ($assets as $g) {
            $assetRows[] = [
                'id' => $g['id'],
                'label' => $g['label'],
                'amount' => number_format($g['total'], 2)
            ];
        }

        $liabRows = [];
        foreach ($liabilities as $g) {
            $liabRows[] = [
                'id' => $g['id'],
                'label' => $g['label'],
                'amount' => number_format($g['total'], 2)
            ];
        }

        // ===============================
        // Totals
        // ===============================
        $assetTotal = array_sum(array_column($assets, 'total'));
        $liabTotal  = array_sum(array_column($liabilities, 'total'));

        return response()->json([
            'header' => [
                'title'  => 'Balance Sheet',
                'as_of'  => $end->toDateString(),
                'period' => $start->toDateString() . ' to ' . $end->toDateString(),
            ],
            'liabilities' => [
                'title' => 'Liabilities',
                'rows'  => $liabRows,
                'total' => number_format($liabTotal, 2),
            ],
            'assets' => [
                'title' => 'Assets',
                'rows'  => $assetRows,
                'total' => number_format($assetTotal, 2),
            ],
        ]);
    }

    public function getProfitLossData_ori(Request $request)
    {
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        $verify   = $request->filled('admin_status')
            ?  $request->input('admin_status')
            : 'verify';

        /* ---------- Sales from INVOICES (net), then less refunds (fallback source) ---------- */
        $salesAgg = DB::table('invoices as i')
            ->whereBetween('i.created_at', [$start, $end])
            ->where('i.admin_status', $verify)
            ->when(Schema::hasColumn('invoices', 'status'), fn($q) => $q->where('i.status', '!=', 'Hold'))
            ->selectRaw('
            COALESCE(SUM(i.sub_total),0)         as sum_sub_total,
            COALESCE(SUM(i.commission_amount),0) as sum_commission,
            COALESCE(SUM(i.party_amount),0)      as sum_party
        ')->first();

        $salesNetBeforeRefunds = max(
            0,
            (float)($salesAgg->sum_sub_total ?? 0)
                - ((float)($salesAgg->sum_commission ?? 0) + (float)($salesAgg->sum_party ?? 0))
        );

        $refunds = (float) DB::table('credit_histories as ch')
            ->where('ch.transaction_kind', 'refund')
            ->whereBetween('ch.created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(CASE WHEN ch.type="debit" THEN ch.debit_amount ELSE 0 END),0) as rf')
            ->value('rf');

        // Invoice-side sales (used as fallback when vouchers are not available)
        $salesFromInvoices = $salesNetBeforeRefunds - $refunds;

        // Default Sales Accounts figure (may be overridden by vouchers below)
        $salesAccounts = $salesFromInvoices;

        /* ---------- Availability ---------- */
        $hasV  = Schema::hasTable('vouchers');
        $hasVL = Schema::hasTable('voucher_lines');
        $hasL  = Schema::hasTable('account_ledgers');
        $hasG  = Schema::hasTable('account_groups');
        $hasE  = Schema::hasTable('expenses');
        $hasP  = Schema::hasTable('purchases');

        /* ---------- Common bases ---------- */
        $linesBase = null;
        if ($hasV && $hasVL && $hasL && $hasG) {
            $linesBase = DB::table('voucher_lines as vl')
                ->join('vouchers as v', 'v.id', '=', 'vl.voucher_id')
                ->join('account_ledgers as l', 'l.id', '=', 'vl.ledger_id')
                ->join('account_groups as g', 'g.id', '=', 'l.group_id')
                ->where('v.admin_status', $verify)
                ->whereBetween('v.voucher_date', [$start->toDateString(), $end->toDateString()])
                ->when(Schema::hasColumn('account_ledgers', 'is_deleted'), fn($q) => $q->where('l.is_deleted', 0));
        }

        /* ---------- Sales from VOUCHERS (Income groups affecting gross) ---------- */
        $salesFromVouchers = 0.0;
        if ($linesBase) {
            $salesFromVouchers = (float) (clone $linesBase)
                ->where('g.nature', 'Income')
                ->where('g.affects_gross', 1)
                ->selectRaw("\n                COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0)\n              - COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0) as amt\n            ")
                ->value('amt');

            // When vouchers exist, we take Sales from vouchers as the accounting truth
            $salesAccounts = $salesFromVouchers;
        }

        // EXPENSES: expense_category_id IS the ledger_id
        $expensesBase = null;
        if ($hasE && $hasL && $hasG) {
            $expensesBase = DB::table('expenses as e')
                ->join('account_ledgers as l', 'l.id', '=', 'e.expense_category_id')
                ->join('account_groups as g', 'g.id', '=', 'l.group_id')
                ->whereBetween('e.expense_date', [$start->toDateString(), $end->toDateString()])
                ->when(Schema::hasColumn('expenses', 'verify'), fn($q) => $q->where('e.verify', 'Yes'))
                ->when(Schema::hasColumn('account_ledgers', 'is_deleted'), fn($q) => $q->where('l.is_deleted', 0));
        }

        /* ---------- Group helpers ---------- */
        $allGroups = DB::table('account_groups')
            ->select('id', 'parent_id', 'name', 'nature', 'affects_gross')
            ->get();

        $byParent  = [];
        foreach ($allGroups as $g) {
            $byParent[$g->parent_id ?? 0][] = $g;
        }

        $descendantsOf = function (array $rootIds) use ($byParent) {
            $set = [];
            $queue = $rootIds;
            foreach ($rootIds as $id) $set[$id] = true;
            while ($queue) {
                $pid = array_shift($queue);
                foreach (($byParent[$pid] ?? []) as $child) {
                    if (!isset($set[$child->id])) {
                        $set[$child->id] = true;
                        $queue[] = $child->id;
                    }
                }
            }
            return array_keys($set);
        };

        /* ---------- Purchase root(s) ---------- */
        $purchaseRootIds = DB::table('account_groups')
            ->whereIn(DB::raw('LOWER(name)'), ['purchase accounts', 'purchase account', 'purchases', 'purchase a/c', 'purchase'])
            ->pluck('id')->all();
        $purchaseGroupIds = $purchaseRootIds ? $descendantsOf($purchaseRootIds) : [];

        /* ---------- Purchase Accounts (purchases + vouchers) → group → ledger ---------- */
        $purchaseFromPurchasesAndVouchers = function () use ($hasP, $hasL, $hasG, $start, $end, $purchaseGroupIds, $linesBase) {
            if (!$hasL || !$hasG || empty($purchaseGroupIds)) {
                return ['total' => 0.0, 'children' => []];
            }

            $acc = [];

            // From purchases table
            // if ($hasP) {
            //     $puBase = DB::table('purchases as pu')
            //         ->whereBetween('pu.date', [$start->toDateString(), $end->toDateString()])
            //         ->leftJoin('account_ledgers as l', 'l.id', '=', DB::raw('CAST(pu.parchase_ledger AS UNSIGNED)'))
            //         ->leftJoin('account_groups as g', 'g.id', '=', 'l.group_id')
            //         ->whereIn('g.id', $purchaseGroupIds)
            //         ->whereNotNull('l.id');

            //     $puRows = $puBase->selectRaw("\n                g.id as gid, g.name as gname,\n                l.id as lid, l.name as lname,\n                COUNT(*)                                                as bills_inv,\n                COALESCE(SUM(COALESCE(pu.total_amount, pu.total, 0)),0) as amt_inv\n            ")
            //         ->groupBy('g.id', 'g.name', 'l.id', 'l.name')
            //         ->get();

            //     foreach ($puRows as $r) {
            //         $acc[$r->gid]['name'] = $r->gname;
            //         $acc[$r->gid]['ledgers'][$r->lid]['name']  = $r->lname;
            //         $acc[$r->gid]['ledgers'][$r->lid]['amt']   = ($acc[$r->gid]['ledgers'][$r->lid]['amt'] ?? 0) + (float)$r->amt_inv;
            //         $acc[$r->gid]['ledgers'][$r->lid]['bills'] = ($acc[$r->gid]['ledgers'][$r->lid]['bills'] ?? 0) + (int)$r->bills_inv;
            //     }
            // }

            // From voucher_lines (net Dr-Cr)
            if ($linesBase) {
                $vl = (clone $linesBase)->whereIn('g.id', $purchaseGroupIds);
                $vlRows = $vl->selectRaw("\n                g.id as gid, g.name as gname,\n                l.id as lid, l.name as lname,\n                COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0) -\n                COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0) as amt_vch,\n                COUNT(DISTINCT v.id) as bills_vch\n            ")
                    ->groupBy('g.id', 'g.name', 'l.id', 'l.name')
                    ->havingRaw('amt_vch <> 0')
                    ->get();

                foreach ($vlRows as $r) {
                    $acc[$r->gid]['name'] = $r->gname;
                    $acc[$r->gid]['ledgers'][$r->lid]['name']  = $r->lname;
                    $acc[$r->gid]['ledgers'][$r->lid]['amt']   = ($acc[$r->gid]['ledgers'][$r->lid]['amt'] ?? 0) + (float)$r->amt_vch;
                    $acc[$r->gid]['ledgers'][$r->lid]['bills'] = ($acc[$r->gid]['ledgers'][$r->lid]['bills'] ?? 0) + (int)$r->bills_vch;
                }
            }

            // Build rows
            $total = 0.0;
            $children = [];
            foreach ($acc as $gid => $gdata) {
                $ledgers = $gdata['ledgers'] ?? [];
                if (!$ledgers) continue;

                uasort($ledgers, fn($a, $b) => strcmp($a['name'], $b['name']));

                $rows = [];
                $groupTotal = 0.0;

                foreach ($ledgers as $lid => $ld) {
                    $amt = (float)($ld['amt'] ?? 0);
                    if (abs($amt) < 0.00001) continue;
                    $groupTotal += $amt;

                    $row = [
                        'label'     => $ld['name'],
                        'amount'    => number_format($amt, 2),
                        'ledger_id' => (int)$lid,
                    ];
                    if (!empty($ld['bills'])) $row['bills'] = (int)$ld['bills'];
                    $rows[] = $row;
                }

                if (abs($groupTotal) < 0.00001) continue;

                $total += $groupTotal;
                // NOTE: Removed the children-level "Total" row here as requested.

                $children[] = [
                    'label'     => $gdata['name'],
                    'amount'    => number_format($groupTotal, 2),
                    'group_id'  => (int)$gid,
                    'children'  => $rows,
                ];
            }

            usort($children, fn($a, $b) => strcmp($a['label'], $b['label']));
            return ['total' => $total, 'children' => $children];
        };

        /* ---------- Generic group→ledger ---------- */
        $groupWithLedgerChildren = function (callable $scope, string $sign) use ($linesBase) {
            if (!$linesBase) return ['total' => 0.0, 'children' => []];

            $base = $scope(clone $linesBase);
            $expr = $sign === 'expense'
                ? "COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0) -\n               COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0)"
                : "COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0) -\n               COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0)";

            $groups = (clone $base)
                ->selectRaw("g.id as gid, g.name as gname")
                ->groupBy('g.id', 'g.name')
                ->orderBy('g.name')
                ->get();

            $total = 0.0;
            $children = [];

            foreach ($groups as $g) {
                $ledgerRows = (clone $base)
                    ->where('g.id', $g->gid)
                    ->selectRaw("l.id as lid, l.name as lname, {$expr} as amt, COUNT(DISTINCT v.id) as bills")
                    ->groupBy('l.id', 'l.name')
                    ->havingRaw('amt <> 0')
                    ->orderBy('l.name')
                    ->get();

                $ledgerTotal = (float)$ledgerRows->sum('amt');
                if (abs($ledgerTotal) < 0.00001) continue;
                $total += $ledgerTotal;

                $mapped = $ledgerRows->map(function ($r) {
                    $row = [
                        'label'     => $r->lname,
                        'amount'    => number_format((float)$r->amt, 2),
                        'ledger_id' => (int)$r->lid,
                    ];
                    if ((int)$r->bills > 0) $row['bills'] = (int)$r->bills;
                    return $row;
                })->all();

                // $mapped[] = ['label' => 'Total', 'amount' => number_format($ledgerTotal, 2), 'is_total' => true];

                $children[] = [
                    'label'     => $g->gname,
                    'amount'    => number_format($ledgerTotal, 2),
                    'group_id'  => (int)$g->gid,
                    'children'  => $mapped
                ];
            }

            return ['total' => $total, 'children' => $children];
        };

        /* ---------- EXPENSE sections: merge voucher_lines + expenses ---------- */
        $expenseGroupsWithLedgers = function (callable $vlScope, callable $expScope) use ($linesBase, $expensesBase) {
            if (!$linesBase && !$expensesBase) return ['total' => 0.0, 'children' => []];

            $vl = $linesBase    ? $vlScope(clone $linesBase)    : null;
            $ex = $expensesBase ? $expScope(clone $expensesBase) : null;

            $groupRows = collect();
            if ($vl) $groupRows = $groupRows->merge((clone $vl)->selectRaw('g.id as gid, g.name as gname')->groupBy('g.id', 'g.name')->get());
            if ($ex) $groupRows = $groupRows->merge((clone $ex)->selectRaw('g.id as gid, g.name as gname')->groupBy('g.id', 'g.name')->get());
            $groups = $groupRows->unique('gid')->sortBy('gname')->values();

            $total = 0.0;
            $children = [];

            foreach ($groups as $g) {
                $vlLedgers = collect();
                if ($vl) {
                    $vlLedgers = (clone $vl)
                        ->where('g.id', $g->gid)
                        ->selectRaw("\n                        l.id as lid, l.name as lname,\n                        COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0) -\n                        COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0) as amt_vl\n                    ")
                        ->groupBy('l.id', 'l.name')
                        ->havingRaw('amt_vl <> 0')
                        ->get();
                }

                $exLedgers = collect();
                if ($ex) {
                    $exLedgers = (clone $ex)
                        ->where('g.id', $g->gid)
                        ->selectRaw("l.id as lid, l.name as lname, COALESCE(SUM(e.amount),0) as amt_ex")
                        ->groupBy('l.id', 'l.name')
                        ->havingRaw('amt_ex <> 0')
                        ->get();
                }

                $byId = [];
                foreach ($vlLedgers as $r) $byId[$r->lid] = ['name' => $r->lname, 'vl' => (float)$r->amt_vl, 'ex' => 0.0];
                foreach ($exLedgers as $r) {
                    if (!isset($byId[$r->lid])) $byId[$r->lid] = ['name' => $r->lname, 'vl' => 0.0, 'ex' => 0.0];
                    $byId[$r->lid]['ex'] += (float)$r->amt_ex;
                }

                $ledgerRows = [];
                $groupTotal = 0.0;
                foreach ($byId as $lid => $row) {
                    $amt = $row['vl'] + $row['ex']; // Dr total
                    if (abs($amt) < 0.00001) continue;
                    $groupTotal += $amt;
                    $ledgerRows[] = [
                        'label'     => $row['name'],
                        'amount'    => number_format($amt, 2),
                        'ledger_id' => (int)$lid,
                    ];
                }

                if (abs($groupTotal) < 0.00001) continue;
                $total += $groupTotal;

                // NOTE: Removed the children-level "Total" row here as requested.

                $children[] = [
                    'label'     => $g->gname,
                    'amount'    => number_format($groupTotal, 2),
                    'group_id'  => (int)$g->gid,
                    'children'  => $ledgerRows,
                ];
            }

            return ['total' => $total, 'children' => $children];
        };

        /* ---------- Sections ---------- */
        $purchase = $purchaseFromPurchasesAndVouchers();

        $direct = $expenseGroupsWithLedgers(
            function ($q) use ($purchaseGroupIds) {
                $q = $q->where('g.nature', 'Expense')->where('g.affects_gross', 1);
                if (!empty($purchaseGroupIds)) $q->whereNotIn('g.id', $purchaseGroupIds);
                return $q;
            },
            function ($q) use ($purchaseGroupIds) {
                $q = $q->where('g.nature', 'Expense')->where('g.affects_gross', 1);
                if (!empty($purchaseGroupIds)) $q->whereNotIn('g.id', $purchaseGroupIds);
                return $q;
            }
        );

        $indirect = $expenseGroupsWithLedgers(
            fn($q) => $q->where('g.nature', 'Expense')->where(fn($w) => $w->where('g.affects_gross', 0)->orWhereNull('g.affects_gross')),
            fn($q) => $q->where('g.nature', 'Expense')->where(fn($w) => $w->where('g.affects_gross', 0)->orWhereNull('g.affects_gross'))
        );

        $indIncomes = $groupWithLedgerChildren(
            fn($q) => $q->where('g.nature', 'Income')->where(fn($w) => $w->where('g.affects_gross', 0)->orWhereNull('g.affects_gross')),
            'income'
        );

        // Sales children (gross-affecting Income) — groups clickable (from vouchers)
        $salesChildren = [];
        if ($linesBase) {
            $salesChildren = (clone $linesBase)
                ->where('g.nature', 'Income')->where('g.affects_gross', 1)
                ->selectRaw("\n                g.id as gid,\n                g.name as gname,\n                COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0) -\n                COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0) as amt\n            ")
                ->groupBy('g.id', 'g.name')->havingRaw('amt <> 0')->orderBy('g.name')->get()
                ->map(fn($r) => [
                    'label'    => $r->gname,
                    'amount'   => number_format((float)$r->amt, 2),
                    'group_id' => (int)$r->gid,
                ])->all();
        }

        /* ---------- Stock valuation ---------- */
        $valueStock = function (Carbon $targetDate, bool $useOpening): float {
            $rows = DB::table('daily_product_stocks as dps')
                ->whereDate('dps.date', $targetDate->toDateString())
                ->select('dps.product_id', $useOpening
                    ? DB::raw('SUM(dps.opening_stock) as qty')
                    : DB::raw('SUM(dps.closing_stock) as qty'))
                ->groupBy('dps.product_id')->pluck('qty', 'product_id');

            if ($rows->isEmpty()) {
                $lastDates = DB::table('daily_product_stocks as d1')
                    ->whereDate('d1.date', '<=', $targetDate->toDateString())
                    ->select('d1.product_id', DB::raw('MAX(d1.date) as last_date'))
                    ->groupBy('d1.product_id');

                $rows = DB::table('daily_product_stocks as d2')
                    ->joinSub($lastDates, 'ld', fn($j) => $j->on('d2.product_id', '=', 'ld.product_id')->on('d2.date', '=', 'ld.last_date'))
                    ->select('d2.product_id', DB::raw('SUM(d2.closing_stock) as qty'))
                    ->groupBy('d2.product_id')->pluck('qty', 'product_id');
            }

            if ($rows->isEmpty()) return 0.0;

            $ids   = $rows->keys()->all();
            $costs = DB::table('products')->whereIn('id', $ids)->pluck('cost_price', 'id');

            $total = 0.0;
            foreach ($rows as $pid => $qty) $total += ((float)$qty) * (float)($costs[$pid] ?? 0);
            return $total;
        };

        $openingStock = $valueStock($start, true);
        $closingStock = $valueStock($end, false);

        /* ---------- Smart normalize: single-group==section -> link header + show ledgers ---------- */
        $normalizeSection = function (string $sectionLabel, array $data) {
            $children = $data['children'] ?? [];

            if (
                count($children) === 1 &&
                strcasecmp(trim($children[0]['label'] ?? ''), trim($sectionLabel)) === 0
            ) {
                $one = $children[0];
                return [
                    'total'             => (float)($data['total'] ?? 0),
                    'children'          => $one['children'] ?? [],  // render ledgers directly
                    'flatten'           => true,                     // treat children as ledgers
                    'section_group_id'  => $one['group_id'] ?? null // make section header clickable
                ];
            }

            return [
                'total'    => (float)($data['total'] ?? 0),
                'children' => $children,
                'flatten'  => false,
            ];
        };

        $purchaseFx = $normalizeSection('Purchase Accounts', $purchase);
        $directFx   = $normalizeSection('Direct Expenses',   $direct);
        $indirectFx = $normalizeSection('Indirect Expenses', $indirect);
        $indInFx    = $normalizeSection('Indirect Incomes',  $indIncomes);

        /* ---------- Totals & balancing ---------- */
        $purchasesTotal = $purchaseFx['total'];
        $directExp      = $directFx['total'];
        $indirectExp    = $indirectFx['total'];
        $indirectIncTot = $indInFx['total'];

        $tradingDr = $openingStock + $purchasesTotal + $directExp;
        $tradingCr = $salesAccounts + $closingStock;

        $grossProfit = $tradingCr >= $tradingDr ? $tradingCr - $tradingDr : 0.0;
        $grossLoss   = $tradingDr >  $tradingCr ? $tradingDr - $tradingCr : 0.0;
        $tradingTableTotal = max($tradingDr + $grossProfit, $tradingCr + $grossLoss);

        $plDrBase = $indirectExp + $grossLoss;
        $plCrBase = $grossProfit + $indirectIncTot;

        $nettProfit = $plCrBase >= $plDrBase ? $plCrBase - $plDrBase : 0.0;
        $nettLoss   = $plDrBase >  $plCrBase ? $plDrBase - $plCrBase : 0.0;
        $plTableTotal = max($plDrBase + $nettProfit, $plCrBase + $nettLoss);

        /* ---------- Build rows (with section + ids) ---------- */
        $tradingDrRows = [
            ['label' => 'Opening Stock', 'amount' => number_format($openingStock, 2)],
            ['label' => 'Purchase Accounts', 'amount' => number_format($purchasesTotal, 2), 'children' => $purchaseFx['children'], 'flatten' => $purchaseFx['flatten'], 'section' => 'purchase', 'section_group_id' => $purchaseFx['section_group_id'] ?? null],
            ['label' => 'Direct Expenses',  'amount' => number_format($directExp, 2),       'children' => $directFx['children'],  'flatten' => $directFx['flatten'],   'section' => 'direct',   'section_group_id' => $directFx['section_group_id'] ?? null],
            ['label' => 'Gross Profit c/o', 'amount' => number_format($grossProfit, 2)],
        ];

        $tradingCrRows = [
            ['label' => 'Sales Accounts', 'amount' => number_format($salesAccounts, 2), 'children' => $salesChildren, 'section' => 'sales'],
            ['label' => 'Closing Stock',  'amount' => number_format($closingStock, 2)],
            ['label' => 'Gross Loss c/o', 'amount' => number_format($grossLoss, 2)],
        ];

        $plDrRows = [
            ['label' => 'Gross Loss b/f',     'amount' => number_format($grossLoss, 2)],
            ['label' => 'Indirect Expenses',  'amount' => number_format($indirectExp, 2), 'children' => $indirectFx['children'], 'flatten' => $indirectFx['flatten'], 'section' => 'indirect', 'section_group_id' => $indirectFx['section_group_id'] ?? null],
        ];
        if ($nettProfit > 0) $plDrRows[] = ['label' => 'Nett Profit', 'amount' => number_format($nettProfit, 2)];

        $plCrRows = [
            ['label' => 'Gross Profit b/f',  'amount' => number_format($grossProfit, 2)],
            ['label' => 'Indirect Incomes',  'amount' => number_format($indirectIncTot, 2), 'children' => $indInFx['children'], 'flatten' => $indInFx['flatten'], 'section' => 'income',  'section_group_id' => $indInFx['section_group_id'] ?? null],
        ];
        if ($nettLoss > 0) $plCrRows[] = ['label' => 'Nett Loss', 'amount' => number_format($nettLoss, 2)];

        $branchName = 'All Branches';

        return response()->json([
            'header' => [
                'title'  => 'Profit & Loss',
                'period' => $start->toDateString() . ' to ' . $end->toDateString(),
                'branch' => $branchName,
            ],
            'trading' => [
                'dr' => ['title' => 'Trading Account (Dr)', 'rows' => $tradingDrRows],
                'cr' => ['title' => 'Trading Account (Cr)', 'rows' => $tradingCrRows],
                'table_total' => number_format($tradingTableTotal, 2),
            ],
            'pl' => [
                'dr' => ['title' => 'Profit & Loss (Dr)', 'rows' => $plDrRows],
                'cr' => ['title' => 'Profit & Loss (Cr)', 'rows' => $plCrRows],
                'table_total' => number_format($plTableTotal, 2),
            ],
        ]);
    }
}
