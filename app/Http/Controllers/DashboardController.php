<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $start_date = request('start_date');
        $end_date = request('end_date');
        $start = Carbon::parse($start_date)->startOfDay();
        $end   = Carbon::parse($end_date)->endOfDay();

        // Get role name from session
        $roleName = strtolower(session('role_name'));
        // Redirect non-admin users to items.cart
        if ($roleName == "warehouse" || $roleName == "cashier") {
            return redirect()->route('items.cart');
        } else if ($roleName !== 'admin') {
            return redirect(route('dashboard'));
        }

        // Only admin users will reach this point
        $branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');

        $totals = Invoice::selectRaw('
                SUM(total) as total_sales,
                SUM(creditpay) as total_creditpay,
                SUM(total_item_qty) as total_products,
                COUNT(*) as invoice_count
            ')
            ->whereNotIn('status', ['Hold', 'resumed', 'archived'])
            ->first();

        $totals_qty = Inventory::selectRaw('SUM(inventories.quantity) as total_quantity')
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->where('products.is_deleted', 'no')
            ->first();

        $invoice_count = $totals->invoice_count;
        $totalQuantity = $totals_qty->total_quantity;
        $totalSales = $totals->total_sales;
        $total_creditpay = $totals->total_creditpay;
        $totalProducts = $totals->total_products;

        $inventorySummary = \DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(products.cost_price * inventories.quantity) as total_cost_price')
            ->first();

        // If range <= 45 days -> daily; otherwise -> monthly (matches screenshot behaviour)
        $groupDaily = $start->diffInDays($end) <= 45;

        // Change 'created_at' to 'date' if you store invoice date in a DATE column.
        $dateCol = 'created_at';

        $sales_trend = [];

        if ($groupDaily) {
            // Daily buckets
            $rows = DB::table('invoices')
                ->selectRaw("DATE($dateCol) as bucket, SUM(total) as amt")
                ->whereBetween($dateCol, [$start, $end])
                ->where('status', 'Paid')
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            // Build full sequence so missing days show as 0
            $labels = [];
            $data   = [];
            $cursor = $start->copy();
            $map = collect($rows)->keyBy('bucket');

            while ($cursor->lte($end)) {
                $key = $cursor->toDateString();
                $labels[] = $cursor->format('d-M');
                $sum = ($map->get($key)->amt ?? 0);
                $data[] = round($sum / 100000, 2); // to Lakhs
                $cursor->addDay();
            }
        } else {
            // Monthly buckets (like Apr-25, May-25, Jun-25)
            $rows = DB::table('invoices')
                ->selectRaw("DATE_FORMAT($dateCol, '%Y-%m-01') as bucket, SUM(total) as amt")
                ->whereBetween($dateCol, [$start, $end])
                ->where('status', 'Paid')
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            // Build full month sequence
            $labels = [];
            $data   = [];
            $cursor = $start->copy()->startOfMonth();
            $endMonth = $end->copy()->startOfMonth();
            $map = collect($rows)->keyBy('bucket');

            while ($cursor->lte($endMonth)) {
                $key = $cursor->format('Y-m-01');
                $labels[] = $cursor->format('M-\'' . $cursor->format('y')); // Apr-25
                $sum = ($map->get($key)->amt ?? 0);
                $data[] = round($sum / 100000, 2); // to Lakhs
                $cursor->addMonth();
            }
        }

        $guaranteeFulfilled = 0;
        $aedToBePaid = 0;

        $lastPurchase = \App\Models\Purchase::latest('id')->first(['guarantee_fulfilled', 'aed_to_be_paid']);

        if ($lastPurchase) {
            $guaranteeFulfilled = $lastPurchase->guarantee_fulfilled;
            $aedToBePaid = $lastPurchase->aed_to_be_paid;
        }

        $tz = config('app.timezone', 'Asia/Kolkata');
        $now = \Carbon\Carbon::now($tz);

        // $start = $request->filled('start_date')
        //     ? \Carbon\Carbon::parse($request->input('start_date'), $tz)->startOfMonth()
        //     : $now->copy()->subMonths(8)->startOfMonth();

        // $end = $request->filled('end_date')
        //     ? \Carbon\Carbon::parse($request->input('end_date'), $tz)->endOfMonth()
        //     : $now->copy()->endOfMonth();

        if ($start_date && $end_date) {
            // User provided dates
            $start = Carbon::parse($start_date)->startOfDay();
            $end   = Carbon::parse($end_date)->endOfDay();
        } else {
            // No date filters → whole current year
            $start = Carbon::now()->startOfYear();
            $end   = Carbon::now()->endOfYear();
        }

        $branchId = request('branch_id');

        $sales = \DB::table('invoices')
            ->selectRaw("DATE_FORMAT(created_at, '%b') as month, SUM(total) as total")
            ->whereBetween('created_at', [$start, $end])
            // ->when($branchId, fn($q, $v) => $q->where('branch_id', $v))
            ->where(function ($q) {
                $q->where('status', 'Paid')->orWhere('invoice_status', 'paid');
            })
            ->groupBy('month')
            ->orderByRaw("MIN(created_at)")
            ->pluck('total', 'month')
            ->toArray();

        // dd($sales);

        // fill missing months with 0
        $months = [];
        $data_sales = [];
        $period = \Carbon\CarbonPeriod::create($start, '1 month', $end);
        foreach ($period as $m) {
            $label = $m->format('M');
            $months[] = $label;
            $data_sales[] = isset($sales[$label]) ? (float)$sales[$label] : 0;
        }


        // $vendorId = $request->integer('vendor_id');

        $query = \DB::table('purchases')
            ->selectRaw("DATE_FORMAT(date, '%b') as month, SUM(total_amount) as total")
            ->whereBetween('date', [$start, $end])
            // ->when($vendorId, fn($q, $v) => $q->where('vendor_id', $v))
            ->groupBy('month')
            ->orderByRaw("MIN(date)");

        $purchases = $query->pluck('total', 'month')->toArray();

        // Fill buckets with 0 if missing

        $data_pur = [];
        $period = \Carbon\CarbonPeriod::create($start, '1 month', $end);
        foreach ($period as $m) {
            $label = $m->format('M');
            $months[] = $label;
            $data_pur[] = isset($purchases[$label]) ? (float)$purchases[$label] : 0;
        }

        /* ----------------- helpers ----------------- */
        $has = fn($tbl) => Schema::hasTable($tbl);

        $groups = DB::table('account_groups')
            ->select('id', 'parent_id', 'name', 'nature', 'affects_gross')
            ->get();

        $byParent = [];
        foreach ($groups as $g) $byParent[$g->parent_id ?? 0][] = $g;

        $descendantsOf = function (array $rootIds) use ($byParent) {
            $set = [];
            $q = $rootIds;
            foreach ($rootIds as $id) $set[$id] = true;
            while ($q) {
                $pid = array_shift($q);
                foreach ($byParent[$pid] ?? [] as $ch) {
                    if (!isset($set[$ch->id])) {
                        $set[$ch->id] = true;
                        $q[] = $ch->id;
                    }
                }
            }
            return array_keys($set);
        };

        $idsByNames = function (array $names) {
            return DB::table('account_groups')
                ->whereIn(DB::raw('LOWER(name)'), array_map('strtolower', $names))
                ->pluck('id')
                ->all();
        };

        $ledgerIdsUnder = function (array $groupIds) use ($descendantsOf) {
            if (empty($groupIds)) return [];
            $all = $descendantsOf($groupIds);
            return DB::table('account_ledgers')
                ->whereIn('group_id', $all)
                ->when(Schema::hasColumn('account_ledgers', 'is_deleted'), fn($q) => $q->where('is_deleted', 0))
                ->pluck('id')
                ->all();
        };

        $vlBase = null;
        if ($has('vouchers') && $has('voucher_lines')) {
            $vlBase = DB::table('voucher_lines as vl')
                ->join('vouchers as v', 'v.id', '=', 'vl.voucher_id')
                ->join('account_ledgers as l', 'l.id', '=', 'vl.ledger_id')
                ->join('account_groups as g', 'g.id', '=', 'l.group_id')
                ->whereBetween('v.voucher_date', [$start->toDateString(), $end->toDateString()])
                ->when($branchId, fn($q, $v) => $q->where('v.branch_id', $v))
                ->when(Schema::hasColumn('account_ledgers', 'is_deleted'), fn($q) => $q->where('l.is_deleted', 0));
        }

        $sumVlOnLedgers = function ($ledgerIds) use ($vlBase) {
            if (!$vlBase || empty($ledgerIds)) return (object)['dr' => 0.0, 'cr' => 0.0];
            return (clone $vlBase)
                ->whereIn('vl.ledger_id', $ledgerIds)
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0) as dr,
                    COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0) as cr
                ")
                ->first();
        };

        $sumGroupBalance = function (array $groupNames, $preferCr = false) use ($ledgerIdsUnder, $sumVlOnLedgers) {
            $root = DB::table('account_groups')
                ->whereIn(DB::raw('LOWER(name)'), array_map('strtolower', $groupNames))->pluck('id')->all();
            $lids = $ledgerIdsUnder($root);
            $s = $sumVlOnLedgers($lids);
            // Dr − Cr (assets typically positive), or Cr − Dr for liabilities if $preferCr
            $bal = $preferCr ? ((float)$s->cr - (float)$s->dr) : ((float)$s->dr - (float)$s->cr);
            $side = $bal >= 0 ? ($preferCr ? 'Cr' : 'Dr') : ($preferCr ? 'Dr' : 'Cr');
            return ['amount' => abs($bal), 'side' => $side];
        };

        $fmt = fn($n) => number_format((float)$n, 2);

        /* ----------------- Cash / Bank ----------------- */
        $cashRoot = $idsByNames(['Cash-in-Hand', 'Cash', 'Cash in hand']);
        $bankRoot = $idsByNames(['Bank Accounts', 'Banks']);
        $cashBankLedgerIds = $ledgerIdsUnder(array_merge($cashRoot, $bankRoot));

        $cb = $sumVlOnLedgers($cashBankLedgerIds);
        $inflow  = (float)$cb->dr; // money into cash/bank (Dr)
        $outflow = (float)$cb->cr; // money out (Cr)
        $netFlow = $inflow - $outflow;

        // Top Bank Ledgers (by absolute balance)
        $topBank = [];
        if (!empty($bankRoot) && $vlBase) {
            $bankLedgers = $ledgerIdsUnder($bankRoot);
            $topBank = (clone $vlBase)
                ->whereIn('vl.ledger_id', $bankLedgers)
                ->selectRaw("
                    l.id as lid, l.name as lname,
                    COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0) as dr,
                    COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0) as cr
                ")
                ->groupBy('l.id', 'l.name')
                ->get()
                ->map(function ($r) {
                    $bal = (float)$r->dr - (float)$r->cr;
                    return [
                        'id'     => (int)$r->lid,
                        'name'   => $r->lname,
                        'amount' => abs($bal),
                        'side'   => $bal >= 0 ? 'Dr' : 'Cr',
                    ];
                })
                ->sortByDesc('amount')
                ->take(5)
                ->values()
                ->all();
        }

        /* ----------------- Assets / Liabilities (closing within period scope) ----------------- */
        $currentAssets      = $sumGroupBalance(['Current Assets'], false); // Dr − Cr
        $currentLiabilities = $sumGroupBalance(['Current Liabilities'], true); // Cr − Dr

        /* ----------------- Trading details (reuse your P&L logic lite) ----------------- */
        // Sales (net of commission+party) minus refunds
        $salesAgg = DB::table('invoices as i')
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v))
            ->whereBetween('i.created_at', [$start, $end])
            ->when(Schema::hasColumn('invoices', 'status'), fn($q) => $q->where('i.status', '!=', 'Hold'))
            ->selectRaw('
                COALESCE(SUM(i.sub_total),0)         as sum_sub_total,
                COALESCE(SUM(i.commission_amount),0) as sum_commission,
                COALESCE(SUM(i.party_amount),0)      as sum_party
            ')
            ->first();

        $salesNet = max(
            0,
            (float)($salesAgg->sum_sub_total ?? 0) -
                ((float)($salesAgg->sum_commission ?? 0) + (float)($salesAgg->sum_party ?? 0))
        );

        $refunds = (float) DB::table('credit_histories as ch')
            ->where('ch.transaction_kind', 'refund')
            ->when($branchId, fn($q, $v) => $q->where('ch.store_id', $v))
            ->whereBetween('ch.created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(CASE WHEN ch.type="debit" THEN ch.debit_amount ELSE 0 END),0) as rf')
            ->value('rf');

        $salesAccounts = $salesNet - $refunds;

        // Purchase Accounts total (purchases + voucher_lines under Purchase group)
        $purchaseRoot = $idsByNames(['Purchase Accounts', 'Purchase Account', 'Purchases', 'Purchase A/C', 'Purchase']);
        $purchaseGroupIds = $descendantsOf($purchaseRoot);
        $purchaseTotal = 0.0;

        if (!empty($purchaseGroupIds)) {
            // from voucher_lines (Dr − Cr)
            if ($vlBase) {
                $p = (clone $vlBase)->whereIn('g.id', $purchaseGroupIds)
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN vl.dc='Dr' THEN vl.amount ELSE 0 END),0) -
                        COALESCE(SUM(CASE WHEN vl.dc='Cr' THEN vl.amount ELSE 0 END),0) as amt
                    ")->value('amt');
                $purchaseTotal += (float)$p;
            }
            // from purchases table
            if ($has('purchases')) {
                $p2 = DB::table('purchases as pu')
                    ->whereBetween('pu.date', [$start->toDateString(), $end->toDateString()])
                    ->leftJoin('account_ledgers as l', 'l.id', '=', DB::raw('CAST(pu.parchase_ledger AS UNSIGNED)'))
                    ->leftJoin('account_groups as g', 'g.id', '=', 'l.group_id')
                    ->whereIn('g.id', $purchaseGroupIds)
                    ->whereNotNull('l.id')
                    ->selectRaw("COALESCE(SUM(COALESCE(pu.total_amount, pu.total,0)),0) as amt")
                    ->value('amt');
                $purchaseTotal += (float)$p2;
            }
        }

        // Direct & Indirect expenses (including expenses table)
        $directExp = 0.0;
        $indirectExp = 0.0;
        if ($vlBase) {
            $exp = (clone $vlBase)->where('g.nature', 'Expense')
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN g.affects_gross = 1 AND vl.dc='Dr' THEN vl.amount ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN g.affects_gross = 1 AND vl.dc='Cr' THEN vl.amount ELSE 0 END),0) as direct_net,
                    COALESCE(SUM(CASE WHEN (g.affects_gross = 0 OR g.affects_gross IS NULL) AND vl.dc='Dr' THEN vl.amount ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN (g.affects_gross = 0 OR g.affects_gross IS NULL) AND vl.dc='Cr' THEN vl.amount ELSE 0 END),0) as indirect_net
                ")->first();
            $directExp   += (float)($exp->direct_net   ?? 0);
            $indirectExp += (float)($exp->indirect_net ?? 0);
        }

        if ($has('expenses')) {
            $exp2 = DB::table('expenses as e')
                ->join('account_ledgers as l', 'l.id', '=', 'e.expense_category_id')
                ->join('account_groups as g', 'g.id', '=', 'l.group_id')
                ->whereBetween('e.expense_date', [$start->toDateString(), $end->toDateString()])
                ->when($branchId, fn($q, $v) => $q->where('e.branch_id', $v))
                ->when(Schema::hasColumn('expenses', 'verify'), fn($q) => $q->where('e.verify', 'Yes'))
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN g.affects_gross=1 THEN e.amount ELSE 0 END),0)  as direct,
                    COALESCE(SUM(CASE WHEN g.affects_gross=0 OR g.affects_gross IS NULL THEN e.amount ELSE 0 END),0) as indirect
                ")->first();
            $directExp   += (float)($exp2->direct   ?? 0);
            $indirectExp += (float)($exp2->indirect ?? 0);
        }

        // Stock valuation (opening & closing)
        $valueStock = function (Carbon $targetDate, bool $useOpening) use ($branchId): float {
            if (!Schema::hasTable('daily_product_stocks')) return 0.0;
            $rows = DB::table('daily_product_stocks as dps')
                ->when($branchId, fn($q, $v) => $q->where('dps.branch_id', $v))
                ->whereDate('dps.date', $targetDate->toDateString())
                ->select(
                    'dps.product_id',
                    $useOpening ? DB::raw('SUM(dps.opening_stock) as qty') : DB::raw('SUM(dps.closing_stock) as qty')
                )
                ->groupBy('dps.product_id')->pluck('qty', 'product_id');

            if ($rows->isEmpty()) {
                $lastDates = DB::table('daily_product_stocks as d1')
                    ->when($branchId, fn($q, $v) => $q->where('d1.branch_id', $v))
                    ->whereDate('d1.date', '<=', $targetDate->toDateString())
                    ->select('d1.product_id', DB::raw('MAX(d1.date) as last_date'))
                    ->groupBy('d1.product_id');
                $rows = DB::table('daily_product_stocks as d2')
                    ->joinSub($lastDates, 'ld', fn($j) => $j->on('d2.product_id', '=', 'ld.product_id')->on('d2.date', '=', 'ld.last_date'))
                    ->when($branchId, fn($q, $v) => $q->where('d2.branch_id', $v))
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
        $closingStock = $valueStock($end,   false);

        // Gross/Nett
        $tradingDr = $openingStock + $purchaseTotal + $directExp;
        $tradingCr = $salesAccounts + $closingStock;

        $grossProfit = $tradingCr >= $tradingDr ? $tradingCr - $tradingDr : 0.0;
        $grossLoss   = $tradingDr >  $tradingCr ? $tradingDr - $tradingCr : 0.0;

        $plDrBase = $indirectExp + $grossLoss;
        $plCrBase = $grossProfit + 0 /* indirect incomes optional here */;
        $nettProfit = $plCrBase >= $plDrBase ? $plCrBase - $plDrBase : 0.0;
        $nettLoss   = $plDrBase >  $plCrBase ? $plDrBase - $plCrBase : 0.0;

        /* ----------------- Inventory details (best effort) ----------------- */
        $inv = ['closing_value' => $closingStock, 'inwards_value' => null, 'outwards_value' => null];
        if ($has('daily_product_stocks')) {
            // try common column names if present
            $inCol  = Schema::hasColumn('daily_product_stocks', 'inwards')  ? 'inwards'  : (Schema::hasColumn('daily_product_stocks', 'inward')  ? 'inward'  : null);
            $outCol = Schema::hasColumn('daily_product_stocks', 'outwards') ? 'outwards' : (Schema::hasColumn('daily_product_stocks', 'outward') ? 'outward' : null);

            if ($inCol || $outCol) {
                $agg = DB::table('daily_product_stocks')
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->selectRaw(($inCol ? "COALESCE(SUM($inCol),0)" : "0") . " as inw, " . ($outCol ? "COALESCE(SUM($outCol),0)" : "0") . " as outw")
                    ->first();
                $inv['inwards_value']  = (float)($agg->inw  ?? 0);
                $inv['outwards_value'] = (float)($agg->outw ?? 0);
            }
        }

        /* ---------- >>> NEW: Receivables / Payables ---------- */
        // From group balances of Sundry Debtors / Sundry Creditors
        $receivables = $sumGroupBalance(['Sundry Debtors'], false); // Dr − Cr
        $payables    = $sumGroupBalance(['Sundry Creditors'], true); // Cr − Dr

        // If you later have due-dates, compute "overdue_*" here.

        /* ---------- >>> NEW: Accounting Ratios ---------- */
        // Inventory Turnover = COGS / Average Inventory
        $cogs = ($openingStock + $purchaseTotal + $directExp) - $closingStock;
        $avgInv = ($openingStock + $closingStock) / 2.0;
        $inventoryTurnover = ($avgInv > 0) ? ($cogs / $avgInv) : null;

        // Debt/Equity = (Loans + Current Liabilities) / (Capital + Reserves)
        $debt   = $sumGroupBalance(['Loans (Liability)', 'Current Liabilities'], true)['amount'];
        $equity = $sumGroupBalance(['Capital Account', 'Reserves & Surplus'], true)['amount'];
        $debtEquity = ($equity > 0) ? ($debt / $equity) : null;

        // Receivable Turnover in Days (DSO) = (AR / Net Sales) * Days
        $daysInPeriod = max(1, $start->diffInDays($end) + 1);
        $salesBase = $salesAccounts > 0 ? $salesAccounts : null;
        $dso = ($salesBase !== null) ? (($receivables['amount'] / $salesBase) * $daysInPeriod) : null;

        // Return on Investment (%) ~ Nett Profit / (Capital + Reserves)
        $roi = ($equity > 0) ? (($nettProfit / $equity) * 100.0) : null;




        // dd($data);

        $data = [
            'store'         => "Selete Store",
            'sales'         => $totalSales + $total_creditpay,
            'products'        => $totalProducts,
            'total_cost_price'     => $inventorySummary->total_cost_price,
            'top_products'  => $totalSales,
            'total_quantity' => $totalQuantity,
            'invoice_count' => $invoice_count,
            'sales_trend' => response()->json([
                'categories' => $labels,
                'series' => [
                    ['name' => 'Net Transactions', 'data' => $data]
                ],
                'range_text' => $start->format('j-M-y') . ' to ' . $end->format('j-M-y'),
            ]),
            'guaranteeFulfilled' => $guaranteeFulfilled,
            'aedToBePaid' => $aedToBePaid,
            'data_sales' => $data_sales,
            'data_pur' => $data_pur,
            'assets_liabilities' => [
                ['label' => 'Current Assets',      'closing' => $fmt($currentAssets['amount']),      'side' => $currentAssets['side']],
                ['label' => 'Current Liabilities', 'closing' => $fmt($currentLiabilities['amount']), 'side' => $currentLiabilities['side']],
            ],
            'cash_flow' => [
                'inflow'  => $fmt($inflow),
                'outflow' => $fmt($outflow),
                'net'     => $fmt($netFlow),
            ],
            'cash_bank_accounts' => [
                'closing' => $fmt(abs($inflow - $outflow)),
                'side'    => ($inflow - $outflow) >= 0 ? 'Dr' : 'Cr',
            ],
            'top_bank_ledgers' => array_map(fn($r) => [
                'id' => $r['id'],
                'name' => $r['name'],
                'closing' => $fmt($r['amount']),
                'side' => $r['side']
            ], $topBank),
            'trading_details' => [
                'gross_profit'     => $fmt($grossProfit),
                'nett_profit'      => $fmt($nettProfit),
                'sales_accounts'   => $fmt($salesAccounts),
                'purchase_accounts' => $fmt($purchaseTotal),
            ],
            'inventory' => [
                'closing_value' => $fmt($inv['closing_value']),
                'inwards_value' => $inv['inwards_value']  !== null ? $fmt($inv['inwards_value'])  : null,
                'outwards_value' => $inv['outwards_value'] !== null ? $fmt($inv['outwards_value']) : null,
            ],
            'receivablesPayables' => [
                'receivables'         => $fmt($receivables['amount']),
                'overdue_receivables' => null, // not available -> keep null
                'payables'            => $fmt($payables['amount']),
                'overdue_payables'    => null, // not available -> keep null
            ],
            'ratios' => [
                'inventory_turnover'       => $inventoryTurnover !== null ? $fmt($inventoryTurnover, 2) : null,
                'debt_equity'              => $debtEquity       !== null ? $fmt($debtEquity, 2)       : null,
                'receivable_turnover_days' => $dso              !== null ? $fmt($dso, 2)              : null,
                'roi_percent'              => $roi              !== null ? $fmt($roi, 2)               : null,
            ]
            // You can add receivables/payables tiles once you confirm tables for parties & settlements.
        ];
        return view('dashboard', compact('branch', 'data')); // This refers to resources/views/dashboard.blade.php
    }

    public function showStore($storeId)
    {
        $start_date = request('start_date');
        $end_date = request('end_date');
        $store = Branch::findOrFail($storeId); // or Branch if you're using Branch model
        $data = $this->getDashboardDataForStore($storeId, $start_date, $end_date); // Your logic here
        return view('dashboard', compact('store', 'data'));
    }

    protected function getDashboardDataForStore($storeId, $start_date = "", $end_date = "")
    {
        // Example: Fetch store
        $store = Branch::findOrFail($storeId);
        $totalsQuery = Invoice::selectRaw('
            SUM(total) as total_sales,
            SUM(creditpay) as total_creditpay,
            SUM(total_item_qty) as total_products,
            COUNT(*) as invoice_count
            ')
            ->where('branch_id', $storeId)
            ->whereNotIn('status', ['Hold', 'resumed', 'archived']);

        if (!empty($start_date) && !empty($end_date)) {
            // Between two dates
            $totalsQuery->whereBetween('created_at', [$start_date, $end_date]);
        }

        $totals = $totalsQuery->first();

        $totalSales = $totals->total_sales;
        $total_creditpay = $totals->total_creditpay;
        $totalProducts = $totals->total_products;
        $invoice_count = $totals->invoice_count;

        $inventorySummaryQuery = \DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(products.cost_price * inventories.quantity) as total_cost_price')
            ->where('inventories.store_id', $storeId);

        $totalsQtyQuery = Inventory::selectRaw('SUM(inventories.quantity) as total_quantity')
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->where('products.is_deleted', 'no');
        if (!empty($start_date) && !empty($end_date)) {
            // Between two dates
            $inventorySummaryQuery->whereBetween('inventories.created_at', [$start_date, $end_date]);
            $totalsQtyQuery->whereBetween('inventories.created_at', [$start_date, $end_date]);
        }

        $inventorySummary = $inventorySummaryQuery->first();
        $totals_qty = $totalsQtyQuery->first();

        $totalQuantity = $totals_qty->total_quantity;

        return [
            'store'         => $store->name,
            'sales'         => $totalSales + $total_creditpay,
            'products'        => $totalProducts,
            'total_cost_price'     => $inventorySummary->total_cost_price,
            'top_products'  => $totalSales,
            'total_quantity' => $totalQuantity,
            'invoice_count' => $invoice_count
        ];


        // Example: Fetch orders/sales for this store
        // $totalSales = Invoice::where('branch_id', $storeId)
        //     // ->whereDate('created_at', today())
        //      ->sum('total');

        // // // Example: Total number of orders today
        // $totalProducts = Invoice::where('branch_id', $storeId)
        //    // ->whereDate('created_at', today())
        //     ->sum('total_item_qty');

        // // // Example: Inventory count for this store


        // $inventorySummary = \DB::table('inventories')
        // ->join('products', 'inventories.product_id', '=', 'products.id')
        // ->where('inventories.store_id', $storeId)
        //  ->selectRaw('SUM(products.cost_price * inventories.quantity) as total_cost_price')
        // ->first();

        // // Example: Top selling products
        // $topProducts = OrderItem::whereHas('order', function ($query) use ($storeId) {
        //         $query->where('store_id', $storeId);
        //     })
        //     ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
        //     ->groupBy('product_id')
        //     ->orderByDesc('total_qty')
        //     ->with('product') // assuming relationship exists
        //     ->take(5)
        //     ->get();

        // return [
        //     'store'         => $store->name,
        //     'sales'         => $totalSales,
        //     'products'        => $totalProducts,
        //     'total_cost_price'     => $inventorySummary->total_cost_price,
        //     'top_products'  => $totalSales,
        // ];
    }
}
