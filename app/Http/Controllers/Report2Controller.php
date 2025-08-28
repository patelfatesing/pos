<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\SubCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class Report2Controller extends Controller
{
    public function productWise(Request $request)
    {
        $partyUsers = DB::table('party_users')
            ->select('id', 'first_name')
            ->where('is_delete', 'No')->where('status', 'Active')->orderBy('first_name')->get();

        $commissionUsers = DB::table('commission_users')
            ->select('id', 'first_name')
            ->where('is_deleted', 'No')->where('status', 'Active')->orderBy('first_name')->get();

        return view('reports.discount_product', compact('partyUsers', 'commissionUsers'));
    }

    public function getProductWiseData(Request $request)
    {
        $branchId          = $request->integer('branch_id');
        $scope             = $request->input('discount_scope', 'all');   // 'all'|'party'|'commission'
        $partyUserId       = $request->integer('party_user_id');
        $commissionUserId  = $request->integer('commission_user_id');
        $searchValue       = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        /* -------- Product map (for fallbacks & names) -------- */
        $products = DB::table('products as p')
            ->select('p.id', 'p.name', 'p.sku', 'p.sell_price')
            ->where('p.is_active', 'yes')->where('p.is_deleted', 'no')
            ->get();

        $productMap = [];
        foreach ($products as $p) {
            $productMap[$p->id] = [
                'name' => $p->name,
                'sku'  => $p->sku,
                'sell' => (float)($p->sell_price ?? 0),
            ];
        }

        /* -------- Invoices with filters -------- */
        $invQ = DB::table('invoices as i')
            ->select(
                'i.id',
                'i.items',
                'i.tax',
                'i.branch_id',
                'i.created_at',
                'i.party_user_id',
                'i.commission_user_id',
                'i.party_amount',
                'i.commission_amount',
                'i.status'
            )
            ->where('i.status', '!=', 'Hold')
            ->whereBetween('i.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v));

        // scope filters
        if ($scope === 'party') {
            $invQ->where('i.party_amount', '>', 0);
            if ($partyUserId) $invQ->where('i.party_user_id', $partyUserId);
        } elseif ($scope === 'commission') {
            $invQ->where('i.commission_amount', '>', 0);
            if ($commissionUserId) $invQ->where('i.commission_user_id', $commissionUserId);
        } else {
            // 'all' — optional guard to skip invoices with zero discounts
            $invQ->where(function ($q) {
                $q->where('i.party_amount', '>', 0)->orWhere('i.commission_amount', '>', 0);
            });
        }

        $invoices = $invQ->get();

        /* -------- Accumulate per product -------- */
        $perProduct = []; // pid => [qty,gross,party,commission,total_discount,net_sales]

        // STRICT resolver: DO NOT read line totals/mrp/rate; use line unit price or product sell_price
        $resolveLine = function (array $it, array $prod) {
            $qty = (float)($it['quantity'] ?? $it['qty'] ?? $it['qnt'] ?? 0);

            foreach (['price', 'sell_price', 'unit_price'] as $k) {
                if (isset($it[$k]) && is_numeric($it[$k])) {
                    $unit = (float)$it[$k];
                    return [$qty, $qty * $unit];
                }
            }
            $unit = (float)($prod['sell'] ?? 0);
            return [$qty, $qty * $unit];
        };

        foreach ($invoices as $inv) {
            $items = json_decode($inv->items, true) ?: [];
            if (empty($items)) continue;

            // Determine which discount buckets we consider for this invoice
            $partyDisc = in_array($scope, ['all', 'party'], true) ? max(0, (float)$inv->party_amount) : 0.0;
            $commDisc  = in_array($scope, ['all', 'commission'], true) ? max(0, (float)$inv->commission_amount) : 0.0;

            // Build gross base for proportional allocation
            $lines = [];
            $invoiceGross = 0.0;

            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                if (!$pid || !isset($productMap[$pid])) continue;

                [$qty, $gross] = $resolveLine($it, $productMap[$pid]);
                if ($qty <= 0) continue;

                $lines[] = ['pid' => $pid, 'qty' => $qty, 'gross' => $gross];
                $invoiceGross += $gross;
            }
            if ($invoiceGross <= 0) continue;

            foreach ($lines as $L) {
                $pid   = $L['pid'];
                $qty   = $L['qty'];
                $gross = $L['gross'];
                $share = $gross / $invoiceGross;

                $partyAlloc = $partyDisc * $share;
                $commAlloc  = $commDisc  * $share;
                $totalDisc  = $partyAlloc + $commAlloc;
                $netSales   = max(0.0, $gross - $totalDisc);

                if (!isset($perProduct[$pid])) {
                    $perProduct[$pid] = [
                        'qty'             => 0.0,
                        'gross'           => 0.0,
                        'party_discount'  => 0.0,
                        'commission_disc' => 0.0,
                        'total_discount'  => 0.0,
                        'net_sales'       => 0.0,
                        'product_name'    => $productMap[$pid]['name'] ?? ('#' . $pid),
                        'sku'             => $productMap[$pid]['sku']  ?? '',
                    ];
                }

                $perProduct[$pid]['qty']             += $qty;
                $perProduct[$pid]['gross']           += $gross;
                $perProduct[$pid]['party_discount']  += $partyAlloc;
                $perProduct[$pid]['commission_disc'] += $commAlloc;
                $perProduct[$pid]['total_discount']  += $totalDisc;
                $perProduct[$pid]['net_sales']       += $netSales;
            }
        }

        // Convert to rows
        $rows = collect($perProduct)->map(function ($m, $pid) {
            return [
                'product_id'       => (int)$pid,
                'product_name'     => $m['product_name'],
                'sku'              => $m['sku'],
                'qty'              => round($m['qty'], 2),
                'gross'            => round($m['gross'], 2),
                'party_discount'   => round($m['party_discount'], 2),
                'commission_disc'  => round($m['commission_disc'], 2),
                'total_discount'   => round($m['total_discount'], 2),
                'net_sales'        => round($m['net_sales'], 2),
            ];
        })->values();

        // Search (product or SKU)
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(function ($r) use ($sv) {
                return str_contains(mb_strtolower($r['product_name']), $sv)
                    || str_contains(mb_strtolower($r['sku']), $sv);
            })->values();
        }

        // Compute grand totals on the filtered set (not page)
        $totals = [
            'qty'             => number_format($rows->sum('qty'), 2),
            'gross'           => number_format($rows->sum('gross'), 2),
            'party_discount'  => number_format($rows->sum('party_discount'), 2),
            'commission_disc' => number_format($rows->sum('commission_disc'), 2),
            'total_discount'  => number_format($rows->sum('total_discount'), 2),
            'net_sales'       => number_format($rows->sum('net_sales'), 2),
        ];

        // Ordering
        $columns = [
            null,              // 0 Sr No
            'product_name',    // 1
            'sku',             // 2
            'qty',             // 3
            'gross',           // 4
            'party_discount',  // 5
            'commission_disc', // 6
            'total_discount',  // 7
            'net_sales',       // 8
            null,              // 9 action
        ];
        if (!empty($request->order)) {
            $idx = (int) $request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'net_sales';
            if ($key) $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('net_sales', SORT_NATURAL, true)->values();
        }

        // Pagination
        $startIdx = (int) $request->start;
        $length   = (int) $request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // Data payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'            => $startIdx + $i + 1,
                'product_name'     => e($r['product_name']),
                'sku'              => e($r['sku']),
                'qty'              => number_format($r['qty'], 2),
                'gross'            => number_format($r['gross'], 2),
                'party_discount'   => number_format($r['party_discount'], 2),
                'commission_disc'  => number_format($r['commission_disc'], 2),
                'total_discount'   => number_format($r['total_discount'], 2),
                'net_sales'        => number_format($r['net_sales'], 2),
                'action'           => '',
            ];
        }

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
            // send grand totals for the footer
            'totals'          => $totals,
        ]);
    }

    public function endDaySummary(Request $request)
    {
        return view('reports.day_end_summary');
    }

    public function getEndDaySummaryData(Request $request)
    {
        $period = $request->input('period', 'today'); // today|yesterday|weekly|monthly|yearly
        $tz     = config('app.timezone', 'Asia/Kolkata');

        // Resolve date range
        $now = Carbon::now($tz);
        switch ($period) {
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end   = $now->copy()->subDay()->endOfDay();
                break;
            case 'weekly':
                $start = $now->copy()->startOfWeek();
                $end   = $now->copy()->endOfWeek();
                break;
            case 'monthly':
                $start = $now->copy()->startOfMonth();
                $end   = $now->copy()->endOfMonth();
                break;
            case 'yearly':
                $start = $now->copy()->startOfYear();
                $end   = $now->copy()->endOfYear();
                break;
            case 'today':
            default:
                $start = $now->copy()->startOfDay();
                $end   = $now->copy()->endOfDay();
                break;
        }

        // Branch name map (once)
        $branchNames = DB::table('branches')->pluck('name', 'id')->toArray();

        // -------- Shift cash (Opening/Closing) grouped by DATE + BRANCH --------
        $shiftAgg = DB::table('shift_closings as s')
            ->selectRaw('
                DATE(s.created_at) as d,
                s.branch_id,
                SUM(COALESCE(s.opening_cash,0)) as opening_cash,
                SUM(COALESCE(s.closing_cash,0)) as closing_cash
            ')
            ->whereBetween('s.created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(s.created_at)'), 's.branch_id')
            ->get();

        // -------- Sales totals & items grouped by DATE + BRANCH --------
        $invAgg = DB::table('invoices as i')
            ->selectRaw('
                DATE(i.created_at) as d,
                i.branch_id,
                SUM(COALESCE(i.total,0)) as total_sales,
                SUM(COALESCE(i.total_item_qty,0)) as items
            ')
            ->where('i.status', '!=', 'Hold')
            ->whereBetween('i.created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(i.created_at)'), 'i.branch_id')
            ->get();

        // -------- Stock variance & sold (for case-dispense) grouped by DATE + BRANCH + PRODUCT --------
        $dpsAgg = DB::table('daily_product_stocks as dps')
            ->selectRaw('
                dps.date as d,
                dps.branch_id,
                dps.product_id,
                SUM(COALESCE(dps.difference_in_stock,0)) as diff_stock,
                SUM(COALESCE(dps.sold_stock,0)) as sold_stock
            ')
            ->whereBetween('dps.date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('dps.date', 'dps.branch_id', 'dps.product_id')
            ->get();

        // Case size map (numeric parser)
        $caseSizeByProduct = DB::table('products')->pluck('case_size', 'id')->toArray();
        $caseSizeNum = function ($raw) {
            if (!is_string($raw) && !is_numeric($raw)) return 0.0;
            $s = (string)$raw;
            if (preg_match('/(\d+(\.\d+)?)/', $s, $m)) return (float)$m[1];
            return 0.0;
        };

        // Merge: key = date|branch
        $byDateBranch = []; // metrics per day+branch
        $ensureKey = function ($date, $branchId) use (&$byDateBranch, $branchNames) {
            $key = $date . '|' . (int)$branchId;
            if (!isset($byDateBranch[$key])) {
                $byDateBranch[$key] = [
                    'date'          => $date,
                    'branch_id'     => (int)$branchId,
                    'branch_name'   => $branchNames[$branchId] ?? ('Branch #' . $branchId),
                    'opening_cash'  => 0.0,
                    'closing_cash'  => 0.0,
                    'total_sales'   => 0.0,
                    'sales_items'   => 0.0,
                    'stock_diff'    => 0.0,
                    'case_dispense' => 0.0,
                ];
            }
            return $key;
        };

        foreach ($shiftAgg as $r) {
            $key = $ensureKey($r->d, (int)$r->branch_id);
            $byDateBranch[$key]['opening_cash'] += (float)$r->opening_cash;
            $byDateBranch[$key]['closing_cash'] += (float)$r->closing_cash;
        }
        foreach ($invAgg as $r) {
            $key = $ensureKey($r->d, (int)$r->branch_id);
            $byDateBranch[$key]['total_sales'] += (float)$r->total_sales;
            $byDateBranch[$key]['sales_items'] += (float)$r->items;
        }
        foreach ($dpsAgg as $r) {
            $key = $ensureKey($r->d, (int)$r->branch_id);
            $byDateBranch[$key]['stock_diff'] += (float)$r->diff_stock;

            $cs = $caseSizeNum($caseSizeByProduct[$r->product_id] ?? null);
            if ($cs > 0) {
                $byDateBranch[$key]['case_dispense'] += ((float)$r->sold_stock / $cs);
            }
        }

        // Build base rows: one row per (date,branch)
        $baseRows = collect(array_values($byDateBranch));

        // Optional search by date or branch
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $baseRows = $baseRows->filter(function ($r) use ($sv) {
                return str_contains(mb_strtolower($r['date']), $sv)
                    || str_contains(mb_strtolower($r['branch_name']), $sv);
            })->values();
        }

        // --- Compute day totals & overall totals on the filtered set ---
        $dayTotals = []; // date => totals
        $overall = [
            'opening_cash'  => 0.0,
            'closing_cash'  => 0.0,
            'total_sales'   => 0.0,
            'sales_items'   => 0.0,
            'stock_diff'    => 0.0,
            'case_dispense' => 0.0,
        ];

        foreach ($baseRows as $r) {
            $d = $r['date'];
            if (!isset($dayTotals[$d])) {
                $dayTotals[$d] = [
                    'opening_cash'  => 0.0,
                    'closing_cash'  => 0.0,
                    'total_sales'   => 0.0,
                    'sales_items'   => 0.0,
                    'stock_diff'    => 0.0,
                    'case_dispense' => 0.0,
                ];
            }
            $dayTotals[$d]['opening_cash']  += (float)$r['opening_cash'];
            $dayTotals[$d]['closing_cash']  += (float)$r['closing_cash'];
            $dayTotals[$d]['total_sales']   += (float)$r['total_sales'];
            $dayTotals[$d]['sales_items']   += (float)$r['sales_items'];
            $dayTotals[$d]['stock_diff']    += (float)$r['stock_diff'];
            $dayTotals[$d]['case_dispense'] += (float)$r['case_dispense'];

            $overall['opening_cash']  += (float)$r['opening_cash'];
            $overall['closing_cash']  += (float)$r['closing_cash'];
            $overall['total_sales']   += (float)$r['total_sales'];
            $overall['sales_items']   += (float)$r['sales_items'];
            $overall['stock_diff']    += (float)$r['stock_diff'];
            $overall['case_dispense'] += (float)$r['case_dispense'];
        }

        // Sort: date desc, then branch name asc
        $baseRows = $baseRows->sortBy([
            ['date', 'desc'],
            ['branch_name', 'asc'],
        ])->values();

        // Compose final rows: branch rows + day total row per date + final overall row
        $final = [];
        $currentDate = null;
        foreach ($baseRows as $r) {
            if ($currentDate !== null && $r['date'] !== $currentDate) {
                // push day total for previous date
                $dt = $dayTotals[$currentDate];
                $final[] = [
                    'row_type'      => 1, // day total
                    'date'          => $currentDate,
                    'branch_name'   => 'All Branches (Day Total)',
                    'opening_cash'  => round($dt['opening_cash'], 2),
                    'closing_cash'  => round($dt['closing_cash'], 2),
                    'total_sales'   => round($dt['total_sales'], 2),
                    'sales_items'   => round($dt['sales_items'], 2),
                    'stock_diff'    => round($dt['stock_diff'], 2),
                    'case_dispense' => round($dt['case_dispense'], 2),
                ];
            }
            // push branch row
            $final[] = [
                'row_type'      => 0, // normal
                'date'          => $r['date'],
                'branch_name'   => $r['branch_name'],
                'opening_cash'  => round((float)$r['opening_cash'], 2),
                'closing_cash'  => round((float)$r['closing_cash'], 2),
                'total_sales'   => round((float)$r['total_sales'], 2),
                'sales_items'   => round((float)$r['sales_items'], 2),
                'stock_diff'    => round((float)$r['stock_diff'], 2),
                'case_dispense' => round((float)$r['case_dispense'], 2),
            ];
            $currentDate = $r['date'];
        }
        // day total for last date (if any)
        if ($currentDate !== null) {
            $dt = $dayTotals[$currentDate];
            $final[] = [
                'row_type'      => 1,
                'date'          => $currentDate,
                'branch_name'   => 'All Branches (Day Total)',
                'opening_cash'  => round($dt['opening_cash'], 2),
                'closing_cash'  => round($dt['closing_cash'], 2),
                'total_sales'   => round($dt['total_sales'], 2),
                'sales_items'   => round($dt['sales_items'], 2),
                'stock_diff'    => round($dt['stock_diff'], 2),
                'case_dispense' => round($dt['case_dispense'], 2),
            ];
        }

        // overall final row
        $final[] = [
            'row_type'      => 2, // overall total
            'date'          => 'Overall Total (All Days)',
            'branch_name'   => 'All Branches',
            'opening_cash'  => round($overall['opening_cash'], 2),
            'closing_cash'  => round($overall['closing_cash'], 2),
            'total_sales'   => round($overall['total_sales'], 2),
            'sales_items'   => round($overall['sales_items'], 2),
            'stock_diff'    => round($overall['stock_diff'], 2),
            'case_dispense' => round($overall['case_dispense'], 2),
        ];

        // Footer totals (same as overall on filtered rows)
        $footerTotals = [
            'opening_cash'  => number_format($overall['opening_cash'], 2),
            'closing_cash'  => number_format($overall['closing_cash'], 2),
            'total_sales'   => number_format($overall['total_sales'], 2),
            'sales_items'   => number_format($overall['sales_items'], 2),
            'stock_diff'    => number_format($overall['stock_diff'], 2),
            'case_dispense' => number_format($overall['case_dispense'], 2),
        ];

        // DataTables: sorting — keep our order stable (date desc, normal rows first, then totals)
        // We'll provide a hidden "row_type" column for secondary sort: 0 < 1 < 2
        // Then by branch name asc (already applied above).
        // If you want to honor client sort, you can re-sort here based on $request->order.

        // Pagination
        $totalRecords = count($final);
        $startIdx = (int) $request->start;
        $length   = (int) $request->length;
        $paged    = $length > 0 ? array_slice($final, $startIdx, $length) : $final;

        // Payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'         => $startIdx + $i + 1,
                'date'          => $r['date'],
                'branch_name'   => $r['branch_name'],
                'opening_cash'  => number_format($r['opening_cash'], 2),
                'closing_cash'  => number_format($r['closing_cash'], 2),
                'total_sales'   => number_format($r['total_sales'], 2),
                'sales_items'   => number_format($r['sales_items'], 2),
                'stock_diff'    => number_format($r['stock_diff'], 2),
                'case_dispense' => number_format($r['case_dispense'], 2),
                'row_type'      => $r['row_type'], // hidden sort key
                'action'        => '',
            ];
        }

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data'            => $data,
            'totals'          => $footerTotals,
        ]);
    }

    public function bestSellingProducts(Request $request)
    {
        $branches = DB::table('branches')
            ->select('id', 'name')
            ->where('is_deleted', 'no')->where('is_active', 'yes')
            ->orderBy('name')->get();

        return view('reports.best_selling', compact('branches'));
    }

    public function getBestSellingProductsData(Request $request)
    {
        // Branch: treat empty/0 as "All"
        $branchIdParam = $request->input('branch_id');
        $branchId = is_numeric($branchIdParam) && (int)$branchIdParam > 0 ? (int)$branchIdParam : null;

        $searchValue = $request->input('search.value');

        // Default date range: last 30 days (today inclusive)
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay(); // last 30 days

        $end = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        // Product names
        $productMap = DB::table('products as p')
            ->select('p.id', 'p.name')
            ->where('p.is_active', 'yes')
            ->where('p.is_deleted', 'no')
            ->pluck('name', 'id'); // product_id => name

        // Invoices in range (+ optional branch)
        $invQ = DB::table('invoices as i')
            ->select('i.items', 'i.branch_id', 'i.created_at')
            ->where('i.status', '!=', 'Hold')
            ->whereBetween('i.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v));

        $invoices = $invQ->get();

        // Aggregate quantities per product (PHP side, JSON-safe)
        $qtyByProduct = []; // product_id => total qty
        foreach ($invoices as $inv) {
            $items = json_decode($inv->items, true) ?: [];
            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                if (!$pid) continue;
                $qty = (float)($it['quantity'] ?? $it['qty'] ?? $it['qnt'] ?? 0);
                if ($qty <= 0) continue;
                $qtyByProduct[$pid] = ($qtyByProduct[$pid] ?? 0) + $qty;
            }
        }

        // Build rows: Product, QTY
        $rows = collect($qtyByProduct)->map(function ($qty, $pid) use ($productMap) {
            return [
                'product_id'   => (int)$pid,
                'product_name' => (string)($productMap[$pid] ?? ('#' . $pid)),
                'qty'          => round((float)$qty, 2),
            ];
        })->values();

        // Search (by product name)
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(
                fn($r) =>
                str_contains(mb_strtolower($r['product_name']), $sv)
            )->values();
        }

        // Totals (on filtered set)
        $totalQty = $rows->sum('qty');

        // Ordering (default: QTY desc)
        $columns = [
            null,            // 0 Sr No
            'product_name',  // 1
            'qty',           // 2
            null,            // 3 action
        ];
        if (!empty($request->order)) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'qty';
            if ($key) $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('qty', SORT_NATURAL, true)->values();
        }

        // Pagination
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // Data payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'        => $startIdx + $i + 1,
                'product_name' => e($r['product_name']),
                'qty'          => $r['qty'],
                'action'       => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
            'totals'          => [
                'qty' => number_format($totalQty, 2),
            ],
        ]);
    }

    public function worstSellingProducts(Request $request)
    {
        $branches = DB::table('branches')
            ->select('id', 'name')
            ->where('is_deleted', 'no')
            ->where('is_active', 'yes')
            ->orderBy('name')
            ->get();

        return view('reports.worst_selling', compact('branches'));
    }

    public function getWorstSellingProductsData(Request $request)
    {
        // Branch: treat empty/0 as "All"
        $branchIdParam = $request->input('branch_id');
        $branchId = is_numeric($branchIdParam) && (int)$branchIdParam > 0 ? (int)$branchIdParam : null;

        $searchValue = $request->input('search.value');

        // Default date range: last 30 days (today inclusive)
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        // Product names
        $productMap = DB::table('products as p')
            ->select('p.id', 'p.name')
            ->where('p.is_active', 'yes')
            ->where('p.is_deleted', 'no')
            ->pluck('name', 'id'); // product_id => name

        // Invoices in range (+ optional branch)
        $invQ = DB::table('invoices as i')
            ->select('i.items', 'i.branch_id', 'i.created_at')
            ->where('i.status', '!=', 'Hold')
            ->whereBetween('i.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v));

        $invoices = $invQ->get();

        // Aggregate quantities per product (PHP side, JSON-safe)
        $qtyByProduct = []; // product_id => total qty
        foreach ($invoices as $inv) {
            $items = json_decode($inv->items, true) ?: [];
            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                if (!$pid) continue;
                $qty = (float)($it['quantity'] ?? $it['qty'] ?? $it['qnt'] ?? 0);
                if ($qty <= 0) continue;
                $qtyByProduct[$pid] = ($qtyByProduct[$pid] ?? 0) + $qty;
            }
        }

        // Build rows: Product, QTY
        $rows = collect($qtyByProduct)->map(function ($qty, $pid) use ($productMap) {
            return [
                'product_id'   => (int)$pid,
                'product_name' => (string)($productMap[$pid] ?? ('#' . $pid)),
                'qty'          => round((float)$qty, 2),
            ];
        })->values();

        // Search (by product name)
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(
                fn($r) =>
                str_contains(mb_strtolower($r['product_name']), $sv)
            )->values();
        }

        // Totals (on filtered set)
        $totalQty = $rows->sum('qty');

        // Ordering (default: QTY asc for "worst" first)
        $columns = [
            null,            // 0 Sr No
            'product_name',  // 1
            'qty',           // 2
            null,            // 3 action
        ];
        if (!empty($request->order)) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'asc');
            $key = $columns[$idx] ?? 'qty';
            $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('qty', SORT_NATURAL, false)->values(); // asc
        }

        // Pagination
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // Data payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'        => $startIdx + $i + 1,
                'product_name' => e($r['product_name']),
                'qty'          => number_format($r['qty'], 2),
                'action'       => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
            'totals'          => [
                'qty' => number_format($totalQty, 2),
            ],
        ]);
    }

    public function notSale(Request $request)
    {
        $branches = DB::table('branches')
            ->select('id', 'name')
            ->where('is_deleted', 'no')
            ->where('is_active', 'yes')
            ->orderBy('name')
            ->get();

        return view('reports.not_sold', compact('branches'));
    }

    public function getNotSaleData(Request $request)
    {
        // Branch: treat empty/0 as “All”
        $branchIdParam = $request->input('branch_id');
        $branchId = is_numeric($branchIdParam) && (int)$branchIdParam > 0 ? (int)$branchIdParam : null;

        $searchValue = $request->input('search.value');

        // Default date range: last 30 days (today inclusive)
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        // --- Products (active) ---
        $products = DB::table('products as p')
            ->select('p.id', 'p.name')
            ->where('p.is_active', 'yes')
            ->where('p.is_deleted', 'no')
            ->orderBy('p.name')
            ->get();

        $productMap = [];
        foreach ($products as $p) {
            $productMap[(int)$p->id] = $p->name;
        }

        // --- Branches to consider ---
        $branchQuery = DB::table('branches')
            ->select('id', 'name')
            ->where('is_deleted', 'no')
            ->where('is_active', 'yes');

        if ($branchId) $branchQuery->where('id', $branchId);

        $branches = $branchQuery->orderBy('name')->get();
        $branchMap = [];
        foreach ($branches as $b) {
            $branchMap[(int)$b->id] = $b->name;
        }

        if ($branches->isEmpty() || empty($productMap)) {
            return response()->json([
                'draw'            => (int)$request->draw,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'totals'          => ['qty' => number_format(0, 2)],
            ]);
        }

        // --- Build sales qty map per (branch, product) for the period ---
        $invQ = DB::table('invoices as i')
            ->select('i.items', 'i.branch_id', 'i.created_at')
            ->where('i.status', '!=', 'Hold')
            ->whereBetween('i.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v));

        $invoices = $invQ->get();

        $sold = []; // $sold[branch_id][product_id] = qty
        foreach ($invoices as $inv) {
            $bId   = (int)($inv->branch_id ?? 0);
            if (!isset($branchMap[$bId])) continue; // ignore branches not in scope

            $items = json_decode($inv->items, true) ?: [];
            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                if (!$pid || !isset($productMap[$pid])) continue;

                $qty = (float)($it['quantity'] ?? $it['qty'] ?? $it['qnt'] ?? 0);
                if ($qty <= 0) continue;

                if (!isset($sold[$bId])) $sold[$bId] = [];
                $sold[$bId][$pid] = ($sold[$bId][$pid] ?? 0) + $qty;
            }
        }

        // --- Build NOT SOLD rows: for each (branch, product) with 0 qty in the period ---
        $rows = collect();
        foreach ($branchMap as $bId => $bName) {
            foreach ($productMap as $pId => $pName) {
                $qty = $sold[$bId][$pId] ?? 0.0;
                if ($qty == 0.0) {
                    $rows->push([
                        'product_id'   => $pId,
                        'product_name' => $pName,
                        'qty'          => 0.0,
                        'branch_id'    => $bId,
                        'branch_name'  => $bName,
                    ]);
                }
            }
        }

        // Optional search (by product name or branch name)
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(function ($r) use ($sv) {
                return str_contains(mb_strtolower($r['product_name']), $sv)
                    || str_contains(mb_strtolower($r['branch_name']), $sv);
            })->values();
        }

        // Totals (QTY will be 0, but keep for consistency)
        $totalQty = $rows->sum('qty');

        // Ordering (default: Branch asc, then Product asc)
        $columns = [
            null,            // 0 Sr No
            'product_name',  // 1
            'qty',           // 2 (always 0)
            'branch_name',   // 3
            null,            // 4 action
        ];
        if (!empty($request->order)) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'asc');
            $key = $columns[$idx] ?? 'branch_name';
            $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('branch_name')->sortBy('product_name')->values();
        }

        // Pagination
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // Data payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'        => $startIdx + $i + 1,
                'product_name' => e($r['product_name']),
                'qty'          => number_format($r['qty'], 2),
                'branch_name'  => e($r['branch_name']),
                'action'       => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
            'totals'          => [
                'qty' => number_format($totalQty, 2),
            ],
        ]);
    }

    public function stockTransfer(Request $request)
    {
        $branches = DB::table('branches')
            ->select('id', 'name')
            ->where('is_deleted', 'no')
            ->where('is_active', 'yes')
            ->orderBy('name')
            ->get();

        return view('reports.stock_transfer', compact('branches'));
    }

    public function getStockTransferData(Request $request)
    {
        $modeParam = $request->input('mode', 'admin'); // 'admin' | 'request'
        $mode = in_array($modeParam, ['admin', 'request']) ? $modeParam : 'admin';

        // Branch filters (empty/0 => ignore)
        $fromParam = $request->input('from_branch_id');
        $toParam   = $request->input('to_branch_id');

        $fromBranchId = is_numeric($fromParam) && (int)$fromParam > 0 ? (int)$fromParam : null;
        $toBranchId   = is_numeric($toParam)   && (int)$toParam   > 0 ? (int)$toParam   : null;

        // Prevent same from & to (server-side safety)
        if ($fromBranchId && $toBranchId && $fromBranchId === $toBranchId) {
            return response()->json([
                'draw' => (int)$request->draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'From and To cannot be the same branch.',
            ]);
        }

        // Default date range = last 30 days (today inclusive)
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        // Branch name cache
        $branchNames = DB::table('branches')->pluck('name', 'id')->toArray();

        $rows = collect();

        if ($mode === 'admin') {
            // =========================
            // ADMIN TRANSFERS (stock_transfers)
            // =========================
            $q = DB::table('stock_transfers as t')
                ->leftJoin('branches as bfrom', 'bfrom.id', '=', 't.from_branch_id')
                ->leftJoin('branches as bto', 'bto.id', '=', 't.to_branch_id')
                ->select(
                    't.id',
                    't.from_branch_id',
                    't.to_branch_id',
                    't.quantity',
                    't.status',
                    't.transferred_at',
                    't.created_at',
                    'bfrom.name as from_name',
                    'bto.name as to_name'
                )
                // Date: prefer transferred_at; if null, fallback to created_at
                ->where(function ($w) use ($start, $end) {
                    $w->whereBetween('t.transferred_at', [$start, $end])
                        ->orWhere(function ($w2) use ($start, $end) {
                            $w2->whereNull('t.transferred_at')
                                ->whereBetween('t.created_at', [$start, $end]);
                        });
                })
                ->when($fromBranchId, fn($qq, $v) => $qq->where('t.from_branch_id', $v))
                ->when($toBranchId,   fn($qq, $v) => $qq->where('t.to_branch_id',   $v))
                ->orderByRaw('COALESCE(t.transferred_at, t.created_at) DESC');

            $transferRows = $q->get();

            foreach ($transferRows as $r) {
                $date = $r->transferred_at ? Carbon::parse($r->transferred_at, $tz) : ($r->created_at ? Carbon::parse($r->created_at, $tz) : null);
                $rows->push([
                    'where'         => (string)($r->to_name   ?? ($branchNames[$r->to_branch_id]   ?? ('#' . $r->to_branch_id))),
                    'from'          => (string)($r->from_name ?? ($branchNames[$r->from_branch_id] ?? ('#' . $r->from_branch_id))),
                    'qty'           => (int)$r->quantity,
                    'status'        => (string)$r->status,
                    'transfer_date' => $date ? $date->format('Y-m-d H:i:s') : '',
                ]);
            }
        } else {
            // =========================
            // REQUESTED STORE (stock_requests)
            // =========================
            $rq = DB::table('stock_requests as r')
                ->leftJoin('branches as b', 'b.id', '=', 'r.store_id')
                ->select(
                    'r.id',
                    'r.store_id',
                    'b.name as store_name',
                    'r.total_request_quantity',
                    'r.status',
                    'r.requested_at'
                )
                ->whereBetween('r.requested_at', [$start, $end])
                ->when($toBranchId,   fn($qq, $v) => $qq->where('r.store_id', $v))
                // if "from" filter is provided, include only requests that have at least one linked transfer with that from branch
                ->when($fromBranchId, function ($qq, $v) {
                    $qq->whereExists(function ($sq) use ($v) {
                        $sq->from('stock_transfers as t')
                            ->whereColumn('t.stock_request_id', 'r.id')
                            ->where('t.from_branch_id', $v);
                    });
                })
                ->orderBy('r.requested_at', 'desc');

            $requests = $rq->get();

            // For display of "From": collect distinct from branches per request (if any)
            $reqIds = $requests->pluck('id')->filter()->values();
            $fromByRequest = [];
            if ($reqIds->count() > 0) {
                $fromRows = DB::table('stock_transfers as t')
                    ->leftJoin('branches as b', 'b.id', '=', 't.from_branch_id')
                    ->select('t.stock_request_id', 't.from_branch_id', 'b.name as from_name')
                    ->whereIn('t.stock_request_id', $reqIds)
                    ->groupBy('t.stock_request_id', 't.from_branch_id', 'b.name') // safe: exact select
                    ->get();

                foreach ($fromRows as $fr) {
                    $rid = (int)$fr->stock_request_id;
                    if (!isset($fromByRequest[$rid])) $fromByRequest[$rid] = [];
                    $fromByRequest[$rid][] = $fr->from_name ?? ($branchNames[$fr->from_branch_id] ?? ('#' . $fr->from_branch_id));
                }
            }

            foreach ($requests as $r) {
                $fromNames = $fromByRequest[$r->id] ?? [];
                $fromLabel = count($fromNames) > 0 ? implode(', ', array_unique($fromNames)) : '—';

                $rows->push([
                    'where'         => (string)($r->store_name ?? ($branchNames[$r->store_id] ?? ('#' . $r->store_id))),
                    'from'          => $fromLabel,
                    'qty'           => (int)$r->total_request_quantity,
                    'status'        => (string)$r->status,
                    'transfer_date' => Carbon::parse($r->requested_at, $tz)->format('Y-m-d H:i:s'),
                ]);
            }
        }

        // DataTables search
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(function ($r) use ($sv) {
                return str_contains(mb_strtolower($r['where']), $sv)
                    || str_contains(mb_strtolower($r['from']), $sv)
                    || str_contains(mb_strtolower($r['status']), $sv)
                    || str_contains(mb_strtolower($r['transfer_date']), $sv);
            })->values();
        }

        // Ordering
        $columns = [
            null,            // 0 Sr No
            'where',         // 1
            'from',          // 2
            'qty',           // 3
            'status',        // 4
            'transfer_date', // 5
            null,            // 6 action
        ];
        if (!empty($request->order)) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'transfer_date';
            if ($key) $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('transfer_date', SORT_NATURAL, true)->values(); // date desc
        }

        // Pagination
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // Payload
        $data = [];
        foreach ($paged as $i => $r) {
            $badge = '<span class="badge ' . $this->statusClass($r['status']) . '">' . e(ucfirst($r['status'])) . '</span>';
            $data[] = [
                'sr_no'         => $startIdx + $i + 1,
                'where'         => e($r['where']),
                'from'          => e($r['from']),
                'qty'           => number_format($r['qty']),
                'status'        => $badge,
                'transfer_date' => e($r['transfer_date']),
                'action'        => '', // add buttons if needed
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
        ]);
    }

    public function purchaseReport(Request $request)
    {
        $vendors = DB::table('vendor_lists')
            ->select('id', 'name')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $subcats = DB::table('sub_categories as sc')
            ->leftJoin('categories as c', 'c.id', '=', 'sc.category_id')
            ->select('sc.id', 'sc.name as subcategory_name', 'c.name as category_name')
            ->where('sc.is_deleted', 'no')->where('sc.is_active', 'yes')
            ->orderBy('c.name')->orderBy('sc.name')
            ->get();

        return view('reports.purchases', compact('vendors', 'subcats'));
    }

    public function getPurchaseReportData(Request $request)
    {
        $vendorId      = $request->integer('vendor_id');
        $subCategoryId = $request->integer('sub_category_id');
        $searchValue   = $request->input('search.value');

        // Default: last 30 days (today inclusive)
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();

        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        $hasProductId = Schema::hasColumn('purchase_products', 'product_id');

        $q = DB::table('purchases as pu')
            ->join('vendor_lists as v', 'v.id', '=', 'pu.vendor_id')
            ->join('purchase_products as pp', 'pp.purchase_id', '=', 'pu.id');

        if ($hasProductId) {
            // Accurate: join by product_id
            $q->leftJoin('products as p', 'p.id', '=', 'pp.product_id');
        } else {
            // Fallback: join by product name = brand_name (adjust to p.brand if that matches your data)
            $q->leftJoin('products as p', function ($join) {
                $join->on('p.name', '=', 'pp.brand_name');
                // If your pp.brand_name actually matches products.brand, use this instead:
                // $join->on('p.brand', '=', 'pp.brand_name');
            });
        }

        $q->leftJoin('sub_categories as sc', 'sc.id', '=', 'p.subcategory_id')
            ->leftJoin('categories as c',    'c.id',  '=', 'p.category_id')
            ->whereBetween('pu.date', [$start->toDateString(), $end->toDateString()])
            ->when($vendorId,      fn($qq, $v) => $qq->where('pu.vendor_id', $v))
            // Subcategory filter only works when we can map a product; with fallback join it still works if names match
            ->when($subCategoryId, fn($qq, $v) => $qq->where('sc.id', $v))
            ->selectRaw('
                pu.id as purchase_id,
                pu.date as ship_date,
                pu.status,
                v.name as vendor_name,
                sc.id as sub_category_id,
                sc.name as subcategory_name,
                c.name  as category_name,
                SUM(COALESCE(pp.qnt,0))     as total_qty,
                SUM(COALESCE(pp.amount,0))  as total_amt
          ')
            ->groupBy(
                'pu.id',
                'pu.date',
                'pu.status',
                'v.name',
                'sc.id',
                'sc.name',
                'c.name'
            );

        $rows = collect($q->get())->map(function ($r) {
            return [
                'vendor_name'   => (string) $r->vendor_name,
                'total_qty'     => (float)  $r->total_qty,
                'total_amt'     => (float)  $r->total_amt,
                'category_name' => (string) ($r->category_name ?? '—'),
                'status'        => (string) $r->status,
                'ship_date'     => (string) $r->ship_date,
            ];
        });

        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(function ($r) use ($sv) {
                return str_contains(mb_strtolower($r['vendor_name']), $sv)
                    || str_contains(mb_strtolower($r['category_name']), $sv)
                    || str_contains(mb_strtolower($r['status']), $sv)
                    || str_contains(mb_strtolower($r['ship_date']), $sv);
            })->values();
        }

        $totalQty = $rows->sum('total_qty');
        $totalAmt = $rows->sum('total_amt');

        // Ordering
        $columns = [
            null,            // 0 Sr No
            'vendor_name',   // 1
            'total_qty',     // 2
            'total_amt',     // 3
            'category_name', // 4
            'status',        // 5
            'ship_date',     // 6
            null,            // 7 action
        ];
        if (!empty($request->order)) {
            $idx = (int) $request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'ship_date';
            if ($key) $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('ship_date', SORT_NATURAL, true)->values();
        }

        // Pagination
        $startIdx = (int) $request->start;
        $length   = (int) $request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // Build payload
        $data = [];
        foreach ($paged as $i => $r) {
            $badge = match (strtolower($r['status'])) {
                'completed' => '<span class="badge bg-success">Completed</span>',
                'approved'  => '<span class="badge bg-info">Approved</span>',
                'rejected'  => '<span class="badge bg-danger">Rejected</span>',
                default     => '<span class="badge bg-secondary">' . e(ucfirst($r['status'])) . '</span>',
            };

            $data[] = [
                'sr_no'         => $startIdx + $i + 1,
                'vendor_name'   => e($r['vendor_name']),
                'total_qty'     => number_format($r['total_qty']),
                'total_amt'     => number_format($r['total_amt'], 2),
                'category_name' => e($r['category_name']),
                'status'        => $badge,
                'ship_date'     => e($r['ship_date']),
                'action'        => '',
            ];
        }

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
            'totals'          => [
                'qty' => number_format($totalQty),
                'amt' => number_format($totalAmt, 2),
            ],
        ]);
    }

    public function purchaseByProductReport(Request $request)
    {
        $vendors = DB::table('vendor_lists')
            ->select('id', 'name')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('reports.purchase_by_product', compact('vendors'));
    }

    public function getPurchaseByProductReportData(Request $request)
    {
        $vendorId    = $request->integer('vendor_id');
        $searchValue = $request->input('search.value');

        // Default: last 30 days (today inclusive)
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();

        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        $hasProductId = Schema::hasColumn('purchase_products', 'product_id');

        $q = DB::table('purchases as pu')
            ->join('vendor_lists as v', 'v.id', '=', 'pu.vendor_id')
            ->join('purchase_products as pp', 'pp.purchase_id', '=', 'pu.id');

        if ($hasProductId) {
            $q->leftJoin('products as p', 'p.id', '=', 'pp.product_id');
        } else {
            // fallback by name; adjust to p.brand = pp.brand_name if your data matches that better
            $q->leftJoin('products as p', function ($join) {
                $join->on('p.name', '=', 'pp.brand_name');
            });
        }

        $q->whereBetween('pu.date', [$start->toDateString(), $end->toDateString()])
            ->when($vendorId, fn($qq, $v) => $qq->where('pu.vendor_id', $v))
            ->selectRaw('
              v.name AS vendor_name,
              COALESCE(p.name, pp.brand_name) AS product_name,
              SUM(COALESCE(pp.qnt,0)) AS qty,
              MAX(COALESCE(p.sell_price, pp.mrp)) AS retail_price,
              SUM(COALESCE(pp.amount,0)) AS total_cost
          ')
            ->groupBy('vendor_name', DB::raw('COALESCE(p.name, pp.brand_name)'));

        $rows = collect($q->get())->map(function ($r) {
            return [
                'vendor_name'  => (string)$r->vendor_name,
                'product_name' => (string)$r->product_name,
                'qty'          => (float)$r->qty,
                'retail_price' => (float)$r->retail_price, // p.sell_price if available, else pp.mrp
                'total_cost'   => (float)$r->total_cost,
            ];
        });

        // Search (vendor or product)
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(
                fn($r) =>
                str_contains(mb_strtolower($r['vendor_name']), $sv) ||
                    str_contains(mb_strtolower($r['product_name']), $sv)
            )->values();
        }

        // Totals (on filtered set)
        $totalQty  = $rows->sum('qty');
        $totalCost = $rows->sum('total_cost');

        // Ordering (default: qty desc)
        $columns = [
            null,            // 0 Sr
            'vendor_name',   // 1
            'product_name',  // 2
            'qty',           // 3
            'retail_price',  // 4
            'total_cost',    // 5
            null,            // 6 action
        ];
        if (!empty($request->order)) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'qty';
            if ($key) $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('qty', SORT_NATURAL, true)->values();
        }

        // Pagination
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // Payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'        => $startIdx + $i + 1,
                'vendor_name'  => e($r['vendor_name']),
                'product_name' => e($r['product_name']),
                'qty'          => number_format($r['qty']),
                'retail_price' => number_format($r['retail_price'], 2),
                'total_cost'   => number_format($r['total_cost'], 2),
                'action'       => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
            'totals'          => [
                'qty'  => number_format($totalQty),
                'cost' => number_format($totalCost, 2),
            ],
        ]);
    }

    public function closingSummary(Request $request)
    {
        $branches = DB::table('branches')
            ->select('id', 'name')
            ->when(DB::getSchemaBuilder()->hasColumn('branches', 'is_deleted'), fn($q) => $q->where('is_deleted', 'no'))
            ->when(DB::getSchemaBuilder()->hasColumn('branches', 'is_active'),  fn($q) => $q->where('is_active', 'yes'))
            ->orderBy('name')
            ->get();

        return view('reports.closing_summary', compact('branches'));
    }

    public function getClosingSummaryData(Request $request)
    {
        $branchId    = $request->integer('branch_id');
        $searchValue = $request->input('search.value');

        // Default: last 30 days (today inclusive)
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();

        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        /**
         * Avoid double counting:
         * 1) For each (branch_id, date, product_id) take MAX(closing_stock)
         * 2) Sum over products to get branch/day closing sum
         */
        $perProduct = DB::table('daily_product_stocks as dps')
            ->select(
                'dps.branch_id',
                'dps.date',
                'dps.product_id',
                DB::raw('MAX(dps.closing_stock) as close_qty')
            )
            ->whereBetween('dps.date', [$start->toDateString(), $end->toDateString()])
            ->when($branchId, fn($q, $v) => $q->where('dps.branch_id', $v))
            ->groupBy('dps.branch_id', 'dps.date', 'dps.product_id');

        $agg = DB::query()
            ->fromSub($perProduct, 'x')
            ->leftJoin('branches as b', 'b.id', '=', 'x.branch_id')
            ->select(
                'x.branch_id',
                DB::raw('COALESCE(b.name, CONCAT("Branch #", x.branch_id)) as branch_name'),
                'x.date',
                DB::raw('SUM(x.close_qty) as closing_stock')
            )
            ->groupBy('x.branch_id', 'branch_name', 'x.date');

        $rows = collect($agg->get())->map(function ($r) {
            return [
                'branch_id'     => (int) $r->branch_id,
                'branch_name'   => (string) $r->branch_name,
                'date'          => (string) $r->date,
                'closing_stock' => (float) $r->closing_stock,
            ];
        });

        // Search (branch name or date)
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(function ($r) use ($sv) {
                return str_contains(mb_strtolower($r['branch_name']), $sv)
                    || str_contains(mb_strtolower($r['date']), $sv);
            })->values();
        }

        // Totals (on filtered set)
        $totalClosing = $rows->sum('closing_stock');

        // Ordering
        $columns = [
            null,            // 0 Sr No
            'branch_name',   // 1
            'date',          // 2
            'closing_stock', // 3
            null,            // 4 action
        ];
        if (!empty($request->order)) {
            $idx = (int) $request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'date';
            if ($key) $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            // Default: Date desc, then Branch asc
            $rows = $rows->sortBy('branch_name')->sortBy('date', SORT_NATURAL, true)->values();
        }

        // Pagination
        $startIdx = (int) $request->start;
        $length   = (int) $request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // Data payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'         => $startIdx + $i + 1,
                'branch_name'   => e($r['branch_name']),
                'date'          => e($r['date']),
                'closing_stock' => number_format($r['closing_stock']),
                'action'        => '',
            ];
        }

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
            'totals'          => [
                'closing_stock' => number_format($totalClosing),
            ],
        ]);
    }

    public function profitOnSalesInvoice(Request $request)
    {
        $branches = DB::table('branches')
            ->select('id', 'name')
            ->when(DB::getSchemaBuilder()->hasColumn('branches', 'is_deleted'), fn($q) => $q->where('is_deleted', 'no'))
            ->when(DB::getSchemaBuilder()->hasColumn('branches', 'is_active'),  fn($q) => $q->where('is_active', 'yes'))
            ->orderBy('name')
            ->get();

        return view('reports.profit_invoice', compact('branches'));
    }

    public function getProfitOnSalesInvoiceData(Request $request)
    {
        $branchId    = $request->integer('branch_id');
        $searchValue = $request->input('search.value');

        // Default: last 30 days (today inclusive)
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = \Illuminate\Support\Carbon::now($tz);

        $start = $request->filled('start_date')
            ? \Illuminate\Support\Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();

        $end   = $request->filled('end_date')
            ? \Illuminate\Support\Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        // 1) Pull invoices in range (exclude Hold if present)
        $invQ = \DB::table('invoices as i')
            ->leftJoin('branches as b', 'b.id', '=', 'i.branch_id')
            ->select(
                'i.id',
                'i.invoice_number',
                'i.branch_id',
                'b.name as branch_name',
                'i.sub_total',
                'i.commission_amount',
                'i.party_amount',
                'i.payment_mode',
                'i.items',
                'i.status',
                'i.created_at'
            )
            ->whereBetween('i.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v));

        if (\DB::getSchemaBuilder()->hasColumn('invoices', 'status')) {
            $invQ->where('i.status', '!=', 'Hold');
        }

        $invoices = $invQ->orderBy('i.created_at', 'desc')->get();

        // 2) Collect unique product IDs from items to fetch prices/costs in one query
        $allProductIds = [];
        foreach ($invoices as $inv) {
            $items = json_decode($inv->items, true) ?: [];
            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                if ($pid > 0) $allProductIds[$pid] = true;
            }
        }
        $allIds = array_keys($allProductIds);

        // Maps: product_id => sell_price / cost_price
        $sellMap = [];
        $costMap = [];
        if (!empty($allIds)) {
            $prodRows = \DB::table('products')
                ->whereIn('id', $allIds)
                ->select('id', 'sell_price', 'cost_price')
                ->get();
            foreach ($prodRows as $p) {
                $sellMap[(int)$p->id] = (float)($p->sell_price ?? 0);
                $costMap[(int)$p->id] = (float)($p->cost_price ?? 0);
            }
        }

        // Helper: parse numeric strings safely
        $num = static function ($v): float {
            if ($v === null || $v === '') return 0.0;
            if (is_numeric($v)) return (float)$v;
            // remove commas or spaces if any
            return (float)str_replace([',', ' '], '', (string)$v);
        };

        // 3) Build rows & totals
        $rows = collect();
        $totSub    = 0.0;
        $totComm   = 0.0;
        $totParty  = 0.0;
        $totProfit = 0.0;

        foreach ($invoices as $inv) {
            $commission = max(0.0, $num($inv->commission_amount));
            $party      = max(0.0, $num($inv->party_amount));
            $subTotal   = $num($inv->sub_total);

            // Profit as per your rule
            $profit = $subTotal - ($commission + $party);

            // OPTIONAL cross-check (not displayed): product margin from items
            // Σ qty × (sell_price - cost_price). sell_price from products; fallback to item price.
            $productMargin = 0.0;
            $items = json_decode($inv->items, true) ?: [];
            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                $qty = $num($it['quantity'] ?? 0);
                if ($qty <= 0) continue;

                $sell = $sellMap[$pid] ?? $num($it['price'] ?? 0);
                $cost = $costMap[$pid] ?? 0.0;

                $productMargin += $qty * ($sell - $cost);
            }
            // You can log/store $productMargin for audits if you want

            $paymode = $inv->payment_mode;
            if ($paymode === 'online') $paymode = 'UPI'; // your UI normalization

            $rows->push([
                'id'               => (int)$inv->id,
                'invoice_number'   => (string)$inv->invoice_number,
                'branch_name'      => $inv->branch_name ?? ('Branch #' . ($inv->branch_id ?? 0)),
                'sub_total'        => $subTotal,
                'commission_amount' => $commission,
                'party_amount'     => $party,
                'payment_mode'     => (string)($paymode ?? '-'),
                'profit'           => $productMargin,
                'created_at'       => (string)$inv->created_at,
                // 'product_margin' => round($productMargin, 2), // keep internal if needed
            ]);

            $totSub    += $subTotal;
            $totComm   += $commission;
            $totParty  += $party;
            $totProfit += $profit;
        }

        // Search (invoice no, branch, payment mode)
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(function ($r) use ($sv) {
                return str_contains(mb_strtolower($r['invoice_number']), $sv)
                    || str_contains(mb_strtolower($r['branch_name']), $sv)
                    || str_contains(mb_strtolower($r['payment_mode']), $sv);
            })->values();
        }

        $recordsTotal    = $rows->count();
        $recordsFiltered = $recordsTotal;

        // Ordering (default newest first)
        $columns = [
            null,                 // 0 Sr No
            'invoice_number',     // 1
            'branch_name',        // 2
            'sub_total',          // 3
            'commission_amount',  // 4
            'party_amount',       // 5
            'payment_mode',       // 6
            'profit',             // 7
        ];
        if (!empty($request->order)) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'created_at';
            $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('created_at', SORT_NATURAL, true)->values();
        }

        // Pagination
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // Data payload
        $data = [];
        foreach ($paged as $i => $r) {
            $invoiceHtml = '<a href="' . url('/view-invoice/' . $r['id']) . '" class="badge badge-success">'
                . e($r['invoice_number'])
                . '</a>';

            $data[] = [
                'sr_no'             => $startIdx + $i + 1,
                'invoice'           => $invoiceHtml,
                'branch_name'       => e($r['branch_name']),
                'sub_total'         => number_format($r['sub_total'], 2),
                'commission_amount' => number_format($r['commission_amount'], 2),
                'party_amount'      => number_format($r['party_amount'], 2),
                'payment_mode'      => e($r['payment_mode']),
                'profit'            => number_format($r['profit'], 2),
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
            'totals'          => [
                'sub_total' => number_format($totSub, 2),
                'commission' => number_format($totComm, 2),
                'party'     => number_format($totParty, 2),
                'profit'    => number_format($totProfit, 2),
            ],
        ]);
    }

    public function productInactive(Request $request)
    {
        $categories = DB::table('categories')
            ->select('id', 'name')
            ->when(DB::getSchemaBuilder()->hasColumn('categories', 'is_deleted'), fn($q) => $q->where('is_deleted', 'no'))
            ->when(DB::getSchemaBuilder()->hasColumn('categories', 'is_active'),  fn($q) => $q->where('is_active', 'yes'))
            ->orderBy('name')->get();

        $subcats = DB::table('sub_categories as sc')
            ->leftJoin('categories as c', 'c.id', '=', 'sc.category_id')
            ->select('sc.id', 'sc.name as subcategory_name', 'sc.category_id', 'c.name as category_name')
            ->when(DB::getSchemaBuilder()->hasColumn('sc', 'is_deleted'), fn($q) => $q->where('sc.is_deleted', 'no'))
            ->when(DB::getSchemaBuilder()->hasColumn('sc', 'is_active'),  fn($q) => $q->where('sc.is_active', 'yes'))
            ->orderBy('c.name')->orderBy('sc.name')->get();

        return view('reports.product_inactive', compact('categories', 'subcats'));
    }

    public function getProductInactiveData(Request $request)
    {
        $categoryId    = $request->integer('category_id');
        $subCategoryId = $request->integer('sub_category_id');
        $includeDeleted = (bool) $request->boolean('include_deleted', false);
        $searchValue   = $request->input('search.value');

        // Default: last 30 days (created_at)
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        $base = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('sub_categories as sc', 'sc.id', '=', 'p.subcategory_id')
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                'p.brand',
                'p.size',
                'p.sell_price',
                'p.cost_price',
                'p.is_active',
                'p.is_deleted',
                'p.created_at',
                'p.updated_at',
                'c.name as category_name',
                'sc.name as sub_category_name'
            )
            ->where('p.is_active', 'no')
            ->when(!$includeDeleted, fn($q) => $q->where('p.is_deleted', 'no'))
            ->whereBetween('p.created_at', [$start, $end])
            ->when($categoryId, fn($q, $v) => $q->where('p.category_id', $v))
            ->when($subCategoryId, fn($q, $v) => $q->where('p.subcategory_id', $v));

        // total before search
        $totalRecords = (clone $base)->count();

        // search
        if (!empty($searchValue)) {
            $sv = "%{$searchValue}%";
            $base->where(function ($q) use ($sv) {
                $q->where('p.name', 'like', $sv)
                    ->orWhere('p.sku', 'like', $sv)
                    ->orWhere('p.brand', 'like', $sv)
                    ->orWhere('c.name', 'like', $sv)
                    ->orWhere('sc.name', 'like', $sv);
            });
        }

        $filteredRecords = (clone $base)->count();

        // ordering (map DataTables index → DB column)
        $columns = [
            'p.id',            // 0 Sr
            'p.name',          // 1 Product
            'p.sku',           // 2 SKU
            'p.brand',         // 3 Brand
            'p.size',          // 4 Size
            'c.name',          // 5 Category
            'sc.name',         // 6 Subcategory
            'p.sell_price',    // 7 Sell
            'p.cost_price',    // 8 Cost
            'p.is_active',     // 9 Status (we’ll show badge)
            'p.created_at',    //10 Created
            'p.updated_at',    //11 Updated
            'p.id',            //12 Action
        ];

        if ($request->order) {
            $orderIdx = (int) $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'desc';
            $orderCol = $columns[$orderIdx] ?? 'p.updated_at';
            $base->orderBy($orderCol, $orderDir);
        } else {
            $base->orderBy('p.updated_at', 'desc');
        }

        // pagination
        if ($request->length > 0) {
            $base->skip((int)$request->start)->take((int)$request->length);
        }

        $rows = $base->get();

        // build payload
        $data = [];
        $startIdx = (int) $request->start;
        foreach ($rows as $i => $r) {
            $statusBadge = $r->is_deleted === 'yes'
                ? '<span class="badge bg-danger">Deleted</span>'
                : '<span class="badge bg-warning text-dark">Inactive</span>';

            $activateBtn = $r->is_deleted === 'yes'
                ? '' // don’t allow activate for deleted (or change to restore logic if you have it)
                : '<button class="btn btn-sm btn-success" onclick="activateProduct(' . (int)$r->id . ')">Activate</button>';

            $data[] = [
                'sr_no'             => $startIdx + $i + 1,
                'name'              => e($r->name),
                'sku'               => e($r->sku),
                'brand'             => e($r->brand),
                'size'              => e($r->size),
                'category_name'     => e($r->category_name ?? ''),
                'sub_category_name' => e($r->sub_category_name ?? ''),
                'sell_price'        => number_format((float)$r->sell_price, 2),
                'cost_price'        => number_format((float)$r->cost_price, 2),
                'status'            => $statusBadge,
                'created_at'        => $r->created_at ? date('Y-m-d H:i:s', strtotime($r->created_at)) : '',
                'updated_at'        => $r->updated_at ? date('Y-m-d H:i:s', strtotime($r->updated_at)) : '',
                'action'            => $activateBtn,
            ];
        }

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
    }

    public function profitLoss(Request $request)
    {
        $branches = DB::table('branches')
            ->select('id', 'name')
            ->when(DB::getSchemaBuilder()->hasColumn('branches', 'is_deleted'), fn($q) => $q->where('is_deleted', 'no'))
            ->when(DB::getSchemaBuilder()->hasColumn('branches', 'is_active'),  fn($q) => $q->where('is_active', 'yes'))
            ->orderBy('name')->get();

        return view('reports.pnl_tally', compact('branches'));
    }

    public function getProfitLossData(Request $request)
    {
        $branchId = $request->integer('branch_id');
        $tz  = config('app.timezone', 'Asia/Kolkata');
        $now = Carbon::now($tz);

        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : $now->copy()->subDays(29)->startOfDay();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : $now->copy()->endOfDay();

        // ---------- 1) Sales (Net) ----------
        $salesNet = DB::table('invoices as i')
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v))
            ->whereBetween('i.created_at', [$start, $end])
            ->when(DB::getSchemaBuilder()->hasColumn('invoices', 'status'), fn($q) => $q->where('i.status', '!=', 'Hold'))
            ->selectRaw('COALESCE(SUM(i.sub_total - (GREATEST(i.commission_amount,0) + GREATEST(i.party_amount,0))),0) as ns')
            ->value('ns');

        // Refunds reduce Sales (if you track them)
        $refunds = DB::table('credit_histories as ch')
            ->where('ch.transaction_kind', 'refund')
            ->when($branchId, fn($q, $v) => $q->where('ch.store_id', $v))
            ->whereBetween('ch.created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(CASE WHEN ch.type="debit" THEN ch.debit_amount ELSE 0 END),0) as rf')
            ->value('rf');

        $salesAccounts = max(0, (float)$salesNet - (float)$refunds);

        // ---------- 2) Purchases (accounts) ----------
        // purchases table has no branch_id in your schema; computed for the company
        $purchasesTotal = 0.0;
        if (DB::getSchemaBuilder()->hasTable('purchases')) {
            $purchasesTotal = (float) DB::table('purchases as pu')
                ->whereBetween('pu.date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('COALESCE(SUM(pu.total_amount),0) as amt')
                ->value('amt');
        }

        // ---------- 3) Direct & Indirect Expenses ----------
        $directExp = 0.0;
        $indirectExp = 0.0;

        if (DB::getSchemaBuilder()->hasTable('expenses')) {
            $expBase = DB::table('expenses as e')
                ->when($branchId, fn($q, $v) => $q->where('e.branch_id', $v))
                ->whereBetween('e.expense_date', [$start->toDateString(), $end->toDateString()]);

            if (
                DB::getSchemaBuilder()->hasTable('expense_categories') &&
                DB::getSchemaBuilder()->hasColumn('expense_categories', 'type')
            ) {
                $exp = $expBase
                    ->leftJoin('expense_categories as ec', 'ec.id', '=', 'e.expense_category_id')
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN ec.type='direct' THEN e.amount ELSE 0 END),0)  as direct_total,
                        COALESCE(SUM(CASE WHEN ec.type='indirect' OR ec.type IS NULL THEN e.amount ELSE 0 END),0) as indirect_total
                    ")
                    ->first();
                $directExp   = (float) ($exp->direct_total   ?? 0);
                $indirectExp = (float) ($exp->indirect_total ?? 0);
            } else {
                // No 'type' column → treat all as indirect
                $indirectExp = (float) $expBase->selectRaw('COALESCE(SUM(e.amount),0) as t')->value('t');
            }
        }

        // ---------- 4) Stock valuation (Opening & Closing) ----------
        // Helper to find snapshot rows on the target day; if none, fallback to nearest prior day
        $valueStock = function (Carbon $targetDate, bool $useOpening) use ($branchId): float {
            // Try exact day first
            $rows = DB::table('daily_product_stocks as dps')
                ->when($branchId, fn($q, $v) => $q->where('dps.branch_id', $v))
                ->whereDate('dps.date', $targetDate->toDateString())
                ->select(
                    'dps.product_id',
                    $useOpening ? DB::raw('SUM(dps.opening_stock) as qty') : DB::raw('SUM(dps.closing_stock) as qty')
                )
                ->groupBy('dps.product_id')
                ->pluck('qty', 'product_id');

            if ($rows->isEmpty()) {
                // Fallback to latest snapshot <= target date
                $lastDates = DB::table('daily_product_stocks as d1')
                    ->when($branchId, fn($q, $v) => $q->where('d1.branch_id', $v))
                    ->whereDate('d1.date', '<=', $targetDate->toDateString())
                    ->select('d1.product_id', DB::raw('MAX(d1.date) as last_date'))
                    ->groupBy('d1.product_id');

                $rows = DB::table('daily_product_stocks as d2')
                    ->joinSub($lastDates, 'ld', function ($j) {
                        $j->on('d2.product_id', '=', 'ld.product_id')->on('d2.date', '=', 'ld.last_date');
                    })
                    ->when($branchId, fn($q, $v) => $q->where('d2.branch_id', $v))
                    ->select(
                        'd2.product_id',
                        DB::raw('SUM(d2.closing_stock) as qty')
                    ) // closest snapshot’s closing as our qty
                    ->groupBy('d2.product_id')
                    ->pluck('qty', 'product_id');
            }

            if ($rows->isEmpty()) return 0.0;

            $ids = $rows->keys()->all();
            $costs = DB::table('products')
                ->whereIn('id', $ids)
                ->pluck('cost_price', 'id');

            $total = 0.0;
            foreach ($rows as $pid => $qty) {
                $cost = (float) ($costs[$pid] ?? 0);
                $total += ((float)$qty) * $cost;
            }
            return $total;
        };

        $openingStock = $valueStock($start, true);   // opening at start date
        $closingStock = $valueStock($end,   false);  // closing at end date

        // ---------- 5) Trading Account (Gross) ----------
        $tradingDr = $openingStock + $purchasesTotal + $directExp;
        $tradingCr = $salesAccounts + $closingStock;

        $grossProfit  = 0.0;  // positive → profit; negative → gross loss
        $grossLoss    = 0.0;

        if ($tradingCr >= $tradingDr) {
            $grossProfit = $tradingCr - $tradingDr;
        } else {
            $grossLoss = $tradingDr - $tradingCr;
        }

        // Balancing totals for Trading section
        $tradingTotal = max($tradingDr + $grossProfit, $tradingCr + $grossLoss);

        // ---------- 6) Profit & Loss (Net) ----------
        // Bring down/brought forward
        $gp_bf = $grossProfit;     // if there was a gross loss → gp_bf = 0 and we’d carry Gross Loss to CR instead (handled below)

        $nettProfit = 0.0;
        $nettLoss   = 0.0;

        if ($grossProfit > 0) {
            // P&L Cr has GP b/f; Dr has Indirect Expenses + Nett Profit balancing
            if ($gp_bf >= $indirectExp) {
                $nettProfit = $gp_bf - $indirectExp;
            } else {
                // net loss in P&L even though trading was profit (rare if indirect > GP)
                $nettLoss = $indirectExp - $gp_bf;
            }
        } else {
            // Gross loss case: P&L Cr has nothing from GP; Dr has indirect + (nett loss)
            $nettLoss = $indirectExp + $grossLoss; // everything is a loss
        }

        // ---------- 7) Build "Tally-like" rows ----------
        // Trading section
        $drTrading = [
            ['label' => 'Opening Stock',      'amount' => $openingStock],
            ['label' => 'Purchase Accounts',  'amount' => $purchasesTotal],
        ];
        if ($directExp > 0) {
            $drTrading[] = ['label' => 'Direct Expenses', 'amount' => $directExp];
        }
        if ($grossProfit > 0) {
            $drTrading[] = ['label' => 'Gross Profit c/o', 'amount' => $grossProfit];
        }

        $crTrading = [
            ['label' => 'Sales Accounts', 'amount' => $salesAccounts],
            ['label' => 'Closing Stock',  'amount' => $closingStock],
        ];
        if ($grossLoss > 0) {
            $crTrading[] = ['label' => 'Gross Loss c/o', 'amount' => $grossLoss];
        }

        // P&L section
        $drPL = [];
        if ($indirectExp > 0) {
            $drPL[] = ['label' => 'Indirect Expenses', 'amount' => $indirectExp];
        }
        if ($nettProfit > 0) {
            $drPL[] = ['label' => 'Nett Profit', 'amount' => $nettProfit];
        }

        $crPL = [];
        if ($grossProfit > 0) {
            $crPL[] = ['label' => 'Gross Profit b/f', 'amount' => $grossProfit];
        }
        if ($nettLoss > 0) {
            $crPL[] = ['label' => 'Nett Loss', 'amount' => $nettLoss];
        }

        // Bottom totals to display (Tally shows section totals equal)
        $tradingTotalFormatted = number_format($tradingTotal, 2);
        $plTotal = max(
            array_sum(array_column($drPL, 'amount')),
            array_sum(array_column($crPL, 'amount'))
        );
        $plTotalFormatted = number_format($plTotal, 2);

        return response()->json([
            'period' => [
                'start' => $start->toDateString(),
                'end'   => $end->toDateString(),
            ],
            'branch' => $branchId ? (DB::table('branches')->where('id', $branchId)->value('name') ?? ('Branch #' . $branchId)) : 'All Branches',
            'trading' => [
                'dr'     => array_map(fn($r) => ['label' => $r['label'], 'amount' => number_format($r['amount'], 2)], $drTrading),
                'cr'     => array_map(fn($r) => ['label' => $r['label'], 'amount' => number_format($r['amount'], 2)], $crTrading),
                'total'  => $tradingTotalFormatted,
            ],
            'pl' => [
                'dr'     => array_map(fn($r) => ['label' => $r['label'], 'amount' => number_format($r['amount'], 2)], $drPL),
                'cr'     => array_map(fn($r) => ['label' => $r['label'], 'amount' => number_format($r['amount'], 2)], $crPL),
                'total'  => $plTotalFormatted,
            ],
            'raw' => [
                'opening_stock'  => round($openingStock, 2),
                'purchases'      => round($purchasesTotal, 2),
                'direct_exp'     => round($directExp, 2),
                'sales_accounts' => round($salesAccounts, 2),
                'closing_stock'  => round($closingStock, 2),
                'gross_profit'   => round($grossProfit, 2),
                'gross_loss'     => round($grossLoss, 2),
                'indirect_exp'   => round($indirectExp, 2),
                'nett_profit'    => round($nettProfit, 2),
                'nett_loss'      => round($nettLoss, 2),
            ]
        ]);
    }

    private function formatCategory(?string $cat, ?string $sub): string
    {
        if ($cat && $sub) return "{$cat} → {$sub}";
        if ($sub) return $sub;
        return 'Uncategorized';
    }

    private function statusBadge(?string $status): string
    {
        $st = strtolower($status ?? 'pending');
        $class = match ($st) {
            'completed', 'approved' => 'bg-success',
            'pending'              => 'bg-secondary',
            'rejected', 'cancelled' => 'bg-danger',
            default                => 'bg-info',
        };
        return '<span class="badge ' . $class . '">' . e(ucfirst($st)) . '</span>';
    }

    private function statusClass(string $status): string
    {
        // small helper for colored badges
        switch (strtolower($status)) {
            case 'approved':
                return 'bg-info';
            case 'completed':
                return 'bg-success';
            case 'rejected':
                return 'bg-danger';
            case 'pending':
            default:
                return 'bg-secondary';
        }
    }
}
