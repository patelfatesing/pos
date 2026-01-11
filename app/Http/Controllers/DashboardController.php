<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseProduct;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Accounting\VoucherLine;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $start_date = request('start_date');
        $end_date = request('end_date');
        $start = Carbon::parse($start_date)->startOfDay();
        $end   = Carbon::parse($end_date)->endOfDay();
        $selectedFY = $request->query('fy');

        // Get role name from session
        $roleName = strtolower(session('role_name'));
        // Redirect non-admin users to items.cart
        // if ($roleName == "warehouse" || $roleName == "cashier") {
        //     return redirect()->route('items.cart');
        // } else {
        //     return redirect(route('dashboard'));
        // }

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
        $period = CarbonPeriod::create($start, '1 month', $end);
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
        $period = CarbonPeriod::create($start, '1 month', $end);
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

        $months = range(1, 12);

        // exactly 12 values, missing months become 0
        // $series = array_map(fn($m) => (float)($raw[$m] ?? 0), $months);

        // ["Jan","Feb",...,"Dec"]
        // $categories = array_map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)), $months);

        [$fyStart, $fyEnd] = $this->getFinancialYearRange($selectedFY);
        $allDaysOfCurrentMonth = $this->getAllDaysOfCurrentMonth();
        $salesDataByDay = $this->getSalesDataByDay();
        $financialYearDropdown = $this->getLatestFiveFinancialYear();
        $currentFYData = $selectedFY ?? $financialYearDropdown['currentFY'];

        $months = collect(range(0, 11))->map(function ($i) use ($fyStart) {
            return $fyStart->copy()->addMonths($i);
        });

        $categories = $months->map(fn($m) => $m->format('M-y'))->toArray();

        $salesQuantityByMonth = $this->getMonthlyQtyTrend(
            Invoice::class,
            'total_item_qty',
            'Sales Quantity',
            $currentFYData
        );

        $purchaseTrendMonthlyQnt = $this->getMonthlyQtyTrend(
            PurchaseProduct::class,
            'qnt',
            'Purchase Quantity',
            $currentFYData
        );

        $salesAmountTotal = $this->getSalesOverviewChartData($currentFYData);

        $financialYearIncomeAmount = $this->incomeExpenseFinancialChartData('Sales', $currentFYData);
        $financialYearExpenseAmount = $this->incomeExpenseFinancialChartData('Purchase', $currentFYData);
        $pieChart = $this->getPieChartData($currentFYData);
        $topAndWorstProducts = $this->getTopAndWorstProductsByCategory(request('fy'));

        $revenueAndCost = $this->getRevenueCostByFinancialYear(request('fy'));

        $data = [
            'store'         => "Select Store",
            'categories'   => $categories,
            'sales_quantity_by_month'   => $salesQuantityByMonth['series'][0]['data'] ?? [],
            'purchase_quantity_by_month'   => $purchaseTrendMonthlyQnt['series'][0]['data'] ?? [],
            'sales_amount_total'   => $salesAmountTotal['series'],
            'financial_year_income'   => $financialYearIncomeAmount['series'],
            'total_financial_year_income'          => array_sum($financialYearIncomeAmount['series']),
            'financial_year_expenses'   => $financialYearExpenseAmount['series'],
            'total_financial_year_expenses'          => array_sum($financialYearExpenseAmount['series']),
            'all_days_of_current_month'          => $allDaysOfCurrentMonth,
            'sales_quantity_by_day' => $salesDataByDay['quantity'],
            'sales_amount_by_day'   => $salesDataByDay['amount'],
            'financial_year_dropdown'   => $financialYearDropdown['financialYears'],
            'current_fy'   => $currentFYData,
            'pie_branch_name'   => $pieChart['labels'],
            'pie_total_item_qty'   => $pieChart['series'],
            'top_and_worst_product'   => $topAndWorstProducts,
            'revenue_value'   => $revenueAndCost['series'][0]['data'],
            'cost_value'   => $revenueAndCost['series'][1]['data'],
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

    public function showStore($storeId, Request $request)
    {
        $start_date = request('start_date');
        $end_date = request('end_date');
        $store = Branch::findOrFail($storeId); // or Branch if you're using Branch model
        $data = $this->getDashboardDataForStore($storeId, $request,  $start_date, $end_date); // Your logic here
        return view('dashboard', compact('store', 'data'));
    }

    protected function getDashboardDataForStore($storeId, $request, $start_date = "", $end_date = "")
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

        $selectedFY = $request->query('fy');
        [$fyStart, $fyEnd] = $this->getFinancialYearRange($selectedFY);
        $allDaysOfCurrentMonth = $this->getAllDaysOfCurrentMonth();
        $salesDataByDay = $this->getSalesDataByDay();
        $financialYearDropdown = $this->getLatestFiveFinancialYear();
        $currentFYData = $selectedFY ?? $financialYearDropdown['currentFY'];

        $months = collect(range(0, 11))->map(function ($i) use ($fyStart) {
            return $fyStart->copy()->addMonths($i);
        });

        $categories = $months->map(fn($m) => $m->format('M-y'))->toArray();

        $salesQuantityByMonth = $this->getMonthlyQtyTrend(
            Invoice::class,
            'total_item_qty',
            'Sales Quantity',
            $currentFYData
        );

        $purchaseTrendMonthlyQnt = $this->getMonthlyQtyTrend(
            PurchaseProduct::class,
            'qnt',
            'Purchase Quantity',
            $currentFYData
        );

        $salesAmountTotal = $this->getSalesOverviewChartData($currentFYData);
        $financialYearIncomeAmount = $this->incomeExpenseFinancialChartData('Sales', $currentFYData);
        $financialYearExpenseAmount = $this->incomeExpenseFinancialChartData('Purchase', $currentFYData);
        $pieChart = $this->getPieChartData($currentFYData);
        $topAndWorstProducts = $this->getTopAndWorstProductsByCategory(request('fy'));

        return [
            'store'         => $store->name,
            'sales'         => $totalSales + $total_creditpay,
            'products'        => $totalProducts,
            'total_cost_price'     => $inventorySummary->total_cost_price,
            'top_products'  => $totalSales,
            'total_quantity' => $totalQuantity,
            'invoice_count' => $invoice_count,
            'categories'   => $categories,
            'sales_quantity_by_month'   => $salesQuantityByMonth['series'][0]['data'] ?? [],
            'purchase_quantity_by_month'   => $purchaseTrendMonthlyQnt['series'][0]['data'] ?? [],
            'sales_amount_total'   => $salesAmountTotal['series'],
            'financial_year_income'   => $financialYearIncomeAmount['series'],
            'total_financial_year_income'          => array_sum($financialYearIncomeAmount['series']),
            'financial_year_expenses'   => $financialYearExpenseAmount['series'],
            'total_financial_year_expenses'          => array_sum($financialYearExpenseAmount['series']),
            'all_days_of_current_month'          => $allDaysOfCurrentMonth,
            'sales_quantity_by_day' => $salesDataByDay['quantity'],
            'sales_amount_by_day'   => $salesDataByDay['amount'],
            'financial_year_dropdown'   => $financialYearDropdown['financialYears'],
            'current_fy'   => $currentFYData,
            'pie_branch_name'   => $pieChart['labels'],
            'pie_total_item_qty'   => $pieChart['series'],
            'top_and_worst_product'   => $topAndWorstProducts,
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

    private function getFinancialYearRange(?string $fy = null): array
    {
        if ($fy) {
            // FY format: 2023-2024
            [$startYear, $endYear] = explode('-', $fy);

            $start = Carbon::create($startYear, 4, 1)->startOfDay();
            $end   = Carbon::create($endYear, 3, 31)->endOfDay();

            return [$start, $end];
        }

        // fallback → current FY
        $date = now();

        if ($date->month >= 4) {
            $start = Carbon::create($date->year, 4, 1)->startOfDay();
            $end   = Carbon::create($date->year + 1, 3, 31)->endOfDay();
        } else {
            $start = Carbon::create($date->year - 1, 4, 1)->startOfDay();
            $end   = Carbon::create($date->year, 3, 31)->endOfDay();
        }

        return [$start, $end];
    }

    private function getAllDaysOfCurrentMonth(): array
    {

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::today();

        $days = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $days[] = $date->format('j M');
        }

        return $days;
    }

    private function getSalesDataByDay(): array
    {
        $now = now();

        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth   = $now->copy()->endOfMonth();

        // If current month → only till today, else full month
        $lastDay = $now->isCurrentMonth()
            ? $now->day
            : $now->daysInMonth;

        $salesByDay = Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select(
                DB::raw('DAY(created_at) as day'),
                DB::raw('SUM(total_item_qty) as qty'),
                DB::raw('SUM(total) as amount')
            )
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $quantity = [];
        $amount   = [];
        for ($day = 1; $day <= $lastDay; $day++) {
            $quantity[] = (int) ($salesByDay[$day]->qty ?? 0);
            $amount[]   = (float) ($salesByDay[$day]->amount ?? 0);
        }

        return [
            'quantity' => $quantity,
            'amount'   => $amount
        ];
    }

    public function getLatestFiveFinancialYear(): array
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Current Financial Year
        $currentFY = $currentMonth >= 4
            ? $currentYear . '-' . ($currentYear + 1)
            : ($currentYear - 1) . '-' . $currentYear;

        // Last 5 financial years (you can adjust)
        $financialYears = [];
        for ($i = 0; $i < 5; $i++) {
            $start = ($currentMonth >= 4 ? $currentYear : $currentYear - 1) - $i;
            $financialYears[] = $start . '-' . ($start + 1);
        }

        return [
            'currentFY' => $currentFY,
            'financialYears'   => $financialYears
        ];
    }

    private function getMonthlyQtyTrend(
        string $modelClass,
        string $qtyColumn,
        string $seriesName,
        ?string $fy
    ): array {
        [$fyStart, $fyEnd] = $this->getFinancialYearRange($fy);

        // Month labels (Apr → Mar)
        $months = collect(range(0, 11))->map(fn($i) => $fyStart->copy()->addMonths($i));
        $categories = $months->map(fn($m) => $m->format('M-y'))->toArray();

        $qtyData = array_fill(0, 12, 0);

        $rows = $modelClass::whereBetween('created_at', [$fyStart, $fyEnd])
            ->select($qtyColumn, 'created_at')
            ->get();

        foreach ($rows as $row) {
            $created = Carbon::parse($row->created_at);

            $index = ($created->year - $fyStart->year) * 12
                + ($created->month - $fyStart->month);

            if ($index >= 0 && $index < 12) {
                $qtyData[$index] += (int) $row->{$qtyColumn};
            }
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => $seriesName,
                    'data' => $qtyData
                ]
            ]
        ];
    }

    public function salesTrendChart(Request $request)
    {
        return response()->json(
            $this->getMonthlyQtyTrend(
                Invoice::class,
                'total_item_qty',
                'Sales Quantity',
                $request->fy
            )
        );
    }

    public function purchaseTrendChart(Request $request)
    {
        return response()->json(
            $this->getMonthlyQtyTrend(
                PurchaseProduct::class,
                'qnt',
                'Purchase Quantity',
                $request->fy
            )
        );
    }

    private function getSalesOverviewChartData(
        ?string $fy
    ): array {
        [$fyStart, $fyEnd] = $this->getFinancialYearRange($fy);

        // FY months (Apr → Mar)
        $months = collect(range(0, 11))->map(fn($i) => $fyStart->copy()->addMonths($i));
        $categories = $months->map(fn($m) => $m->format('M-y'))->toArray();

        $amountData = array_fill(0, 12, 0);

        $rows = Invoice::whereBetween('created_at', [$fyStart, $fyEnd])
            ->select('total', 'created_at')
            ->get();

        foreach ($rows as $row) {
            $created = Carbon::parse($row->created_at);

            $index = ($created->year - $fyStart->year) * 12
                + ($created->month - $fyStart->month);

            if ($index >= 0 && $index < 12) {
                $amountData[$index] += (float) $row->total;
            }
        }

        return [
            'categories' => $categories,
            'series' => $amountData
        ];
    }

    public function salesOverviewChart(Request $request)
    {
        return response()->json(
            $this->getSalesOverviewChartData(
                $request->fy
            )
        );
    }

    private function incomeExpenseFinancialChartData(
        string $voucherType,
        ?string $fy
    ): array {
        [$fyStart, $fyEnd] = $this->getFinancialYearRange($fy);

        $months = collect(range(0, 11))->map(fn($i) => $fyStart->copy()->addMonths($i));
        $categories = $months->map(fn($m) => $m->format('M-y'))->toArray();

        $series = array_fill(0, 12, 0);

        $lines = VoucherLine::whereBetween('created_at', [$fyStart, $fyEnd])
            ->whereHas('voucher', fn($q) => $q->where('voucher_type', $voucherType))
            ->select('amount', 'created_at')
            ->get();

        foreach ($lines as $line) {
            $created = Carbon::parse($line->created_at);

            $index = ($created->year - $fyStart->year) * 12
                + ($created->month - $fyStart->month);

            if ($index >= 0 && $index < 12) {
                $series[$index] += (float) $line->amount;
            }
        }

        return [
            'categories' => $categories,
            'series'     => $series,
            'total'      => array_sum($series),
        ];
    }

    public function incomeExpenseFinancialChart(Request $request)
    {
        $typeMap = [
            'income'  => 'Sales',
            'expense' => 'Purchase',
        ];

        $type = $request->type;

        if (!isset($typeMap[$type])) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        return response()->json(
            $this->incomeExpenseFinancialChartData(
                $typeMap[$type],
                $request->fy
            )
        );
    }

    public function pieChart(Request $request)
    {
        return response()->json(
            $this->getPieChartData($request->fy)
        );
    }

    private function getPieChartData(?string $fy)
    {
        [$fyStart, $fyEnd] = $this->getFinancialYearRange($fy);

        $data = Invoice::with('branch')
            ->select('branch_id', DB::raw('SUM(total_item_qty) as total_item_qty'))
            ->whereBetween('created_at', [$fyStart, $fyEnd])
            ->groupBy('branch_id')
            ->get();

        return [
            'labels' => $data->pluck('branch.name')->toArray(),
            'series' => $data->pluck('total_item_qty')
                ->map(fn($val) => (int) $val)
                ->toArray(),
        ];
    }

    private function getTopAndWorstProductsByCategory(?string $fy): array
    {
        [$fyStart, $fyEnd] = $this->getFinancialYearRange($fy);

        $subCategories = ['BEER', 'IMFL', 'CL', 'RML'];

        $rows = DB::select("
    WITH RECURSIVE seq(n) AS (
        SELECT 0
        UNION ALL
        SELECT n + 1 FROM seq
    )
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(i.items, CONCAT('$[', seq.n, '].subcategory'))) AS subcategory,
        JSON_EXTRACT(i.items, CONCAT('$[', seq.n, '].product_id')) AS product_id,
        p.name AS product_name,
        SUM(JSON_EXTRACT(i.items, CONCAT('$[', seq.n, '].quantity'))) AS total_qty,
        SUM(
            JSON_EXTRACT(i.items, CONCAT('$[', seq.n, '].quantity')) *
            JSON_EXTRACT(i.items, CONCAT('$[', seq.n, '].price'))
        ) AS total_amount
    FROM invoices i
    JOIN seq
        ON seq.n < JSON_LENGTH(i.items)
    JOIN products p 
        ON p.id = JSON_EXTRACT(i.items, CONCAT('$[', seq.n, '].product_id'))
    WHERE i.created_at BETWEEN ? AND ?
    AND seq.n < 500  -- LIMIT OUTSIDE CTE
    GROUP BY subcategory, product_id, p.name
", [$fyStart, $fyEnd]);


        $collection = collect($rows);

        $top = [];
        $worst = [];

        foreach ($subCategories as $cat) {
            $catData = $collection->where('subcategory', $cat);

            $top[$cat] = $catData->sortByDesc('total_qty')->first();
            $worst[$cat] = $catData->sortBy('total_qty')->first();
        }

        return [
            'top' => $top,
            'worst' => $worst,
        ];
    }

    public function ajaxTopAndWorstProducts(Request $request)
    {
        $data = $this->getTopAndWorstProductsByCategory($request->fy);

        $categoryImages = [
            'BEER' => 'assets/images/subcategory/Beer-Category.jpeg',
            'CL'   => 'assets/images/subcategory/Country-Liqour-Category.jpeg',
            'IMFL' => 'assets/images/subcategory/Imfl-Category.jpeg',
            'RML'  => 'assets/images/subcategory/Rml-Category.jpeg',
        ];

        $products = $request->type === 'top'
            ? $data['top']
            : $data['worst'];

        $bgClass = $request->type === 'top'
            ? 'bg-warning-light'
            : 'bg-danger-light';

        $html = '';

        foreach ($products as $category => $product) {

            if (!$product) continue;

            $img = asset($categoryImages[$category] ?? 'assets/images/default.png');

            $html .= '
        <div class="card card-block card-stretch card-height-helf mb-3">
            <div class="card-body card-item-right">
                <div class="d-flex align-items-top">
                    <div class="' . $bgClass . ' rounded">
                        <img src="' . $img . '"
                            class="style-img m-auto"
                            style="width: 250px; height: 180px;"
                            alt="' . $category . '">
                    </div>
                    <div class="style-text text-left ml-3">
                        <h5 class="mb-1">' . e($product->product_name) . '</h5>
                        <small class="text-muted">' . $category . '</small>
                        <p class="mb-1">Total Sell : ' . number_format($product->total_qty) . '</p>
                        <p class="mb-0">Total Earned : ₹' . number_format($product->total_amount, 2) . '</p>
                    </div>
                </div>
            </div>
        </div>';
        }

        if ($html === '') {
            $html = '<p class="text-center text-muted">No data found</p>';
        }

        return response()->json(['html' => $html]);
    }

    private function getRevenueCostByFinancialYear(?string $fy = null): array
    {
        [$fyStart, $fyEnd] = $this->getFinancialYearRange($fy);

        $rows = VoucherLine::query()
            ->join('account_ledgers as al', 'al.id', '=', 'voucher_lines.ledger_id')
            ->whereBetween('voucher_lines.created_at', [$fyStart, $fyEnd])
            ->selectRaw("
            YEAR(voucher_lines.created_at) as y,
            MONTH(voucher_lines.created_at) as m,

            SUM(
                CASE
                    WHEN al.group_id IN (9,10,11,26) AND voucher_lines.dc = 'Cr'
                        THEN voucher_lines.amount
                    WHEN al.group_id IN (9,10,11,26) AND voucher_lines.dc = 'Dr'
                        THEN -voucher_lines.amount
                    ELSE 0
                END
            ) AS revenue,

            ABS(
                SUM(
                    CASE
                        WHEN al.group_id IN (12,13,14) AND voucher_lines.dc = 'Dr'
                            THEN voucher_lines.amount
                        WHEN al.group_id IN (12,13,14) AND voucher_lines.dc = 'Cr'
                            THEN -voucher_lines.amount
                        ELSE 0
                    END
                )
            ) AS cost
        ")
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($row) => sprintf('%04d-%02d', $row->y, $row->m));

        /* 🔹 FY skeleton Apr → Mar */
        $categories = [];
        $revenue    = [];
        $cost       = [];

        $cursor = $fyStart->copy();

        [$fyStart, $fyEnd] = $this->getFinancialYearRange($fy);

        $months = collect(range(0, 11))->map(fn($i) => $fyStart->copy()->addMonths($i));
        $categories = $months->map(fn($m) => $m->format('M-y'))->toArray();

        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('Y-m');

            $categories[] = $cursor->format('M-y');
            $revenue[]    = isset($rows[$key]) ? (float) $rows[$key]->revenue : 0;
            $cost[]       = isset($rows[$key]) ? (float) $rows[$key]->cost : 0;

            $cursor->addMonth();
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $revenue
                ],
                [
                    'name' => 'Cost',
                    'data' => $cost
                ]
            ]
        ];
    }

    public function revenueVsCostChart(Request $request)
    {
        return response()->json(
            $this->getRevenueCostByFinancialYear($request->fy)
        );
    }
}
