<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\SubCategory;
use Carbon\Carbon;

class ReportController extends Controller
{

    public function index()
    {
        return view('reports.list');
    }

    public function lowLevel()
    {
        $branches = DB::table('branches')->get(); // Adjust if you use a model
        $branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');
        $subcategories = SubCategory::where('is_deleted', 'no')->get();

        return view('reports.low-level', compact('branches', 'subcategories'));
    }

    public function getLowLevelData(Request $request)
    {

        $branchId    = $request->integer('branch_id');
        $status      = $request->string('status')->toString(); // '', 'low', 'out'
        $searchValue = $request->input('search.value');

        // Apply filters/search BEFORE aggregation
        $base = DB::table('inventories as inv')
            ->join('products as p', 'p.id', '=', 'inv.product_id')
            ->leftJoin('branches as b', 'b.id', '=', 'inv.store_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('sub_categories as sc', 'sc.id', '=', 'p.subcategory_id')
            ->where('p.is_active', 'yes')
            ->where('p.is_deleted', 'no')
            ->when($branchId, fn($q, $v) => $q->where('inv.store_id', $v))
            ->when($searchValue, function ($q) use ($searchValue) {
                $q->where(function ($qq) use ($searchValue) {
                    $qq->where('p.name', 'like', "%{$searchValue}%")
                        ->orWhere('c.name', 'like', "%{$searchValue}%")   // category
                        ->orWhere('sc.name', 'like', "%{$searchValue}%")  // subcategory
                        ->orWhere('b.name', 'like', "%{$searchValue}%");
                });
            });

        // Grouped rows: one per (branch, product)
        $grouped = $base->cloneWithout(['columns', 'orders', 'limit', 'offset'])
            ->selectRaw("
            inv.store_id AS branch_id,
            COALESCE(b.name, CONCAT('Branch #', inv.store_id)) AS branch_name,
            p.id AS product_id,
            p.name AS product_name,
            c.name AS category_name,
            sc.name AS sub_category_name,
            COALESCE(p.reorder_level, 0) AS reorder_level,
            SUM(inv.low_level_qty) AS low_level_sum,
            SUM(inv.quantity) AS qty,
            MIN(inv.expiry_date) AS nearest_expiry
        ")
            ->groupBy(
                'inv.store_id',   // for branch_name expression
                'b.name',
                'p.id',
                'p.name',
                'c.name',
                'sc.name',
                'p.reorder_level'
            )
            ->get();

        // Compute status (OK / Low Stock / Out of Stock)
        $rows = $grouped->map(function ($r) {
            $threshold = (int) $r->reorder_level;
            if ($threshold <= 0 && (int)$r->low_level_sum > 0) {
                $threshold = (int) $r->low_level_sum; // fallback to inventories.low_level_qty
            }

            $status = 'OK';
            if ((int)$r->qty <= 0) {
                $status = '<span class ="text-danger">Out of Stock</span>';
            } elseif ($threshold > 0 && (int)$r->qty <= $threshold) {
                $status = '<span class="text-danger">Low Stock</span>';
            }

            return [
                'product_name'     => $r->product_name,
                'category_name'    => $r->category_name,     // 👈 was brand
                'sub_category_name' => $r->sub_category_name, // 👈 was sku
                'branch_name'      => $r->branch_name,
                'qty'              => (int) $r->qty,
                'reorder_level'    => (int) $r->reorder_level,
                'status'           => $status,
                'nearest_expiry'   => $r->nearest_expiry,
                'actions'          => '',
            ];
        });

        // Filter by status if requested
        if ($status === 'low') {
            $rows = $rows->where('status', 'Low Stock')->values();
        } elseif ($status === 'out') {
            $rows = $rows->where('status', 'Out of Stock')->values();
        }

        // Order (default: status priority then qty asc)
        if (!empty($request->order)) {
            $orderColumnIndex = (int) $request->order[0]['column'];
            $orderDir = strtolower($request->order[0]['dir'] ?? 'asc');
            $columns = [
                null,                 // 0 Sr No
                'product_name',       // 1
                'category_name',      // 2 (replaces brand)
                'sub_category_name',  // 3 (replaces sku)
                'branch_name',        // 4
                'qty',                // 5
                'reorder_level',      // 6
                'status',             // 7
                'nearest_expiry',     // 8
                null,                 // 9 actions
            ];
            $key = $columns[$orderColumnIndex] ?? 'status';
            if ($key) {
                $rows = $rows->sortBy($key, SORT_REGULAR, $orderDir === 'desc')->values();
            }
        } else {
            $priority = ['Out of Stock' => 0, 'Low Stock' => 1, 'OK' => 2];
            $rows = $rows->sortBy(
                fn($r) => ($priority[$r['status']] ?? 99) . '|' . str_pad((string)$r['qty'], 10, '0', STR_PAD_LEFT)
            )->values();
        }

        // Pagination
        $start = (int) $request->start;
        $length = (int) $request->length;
        $paged = $length > 0 ? $rows->slice($start, $length)->values() : $rows;

        // Totals for DataTables
        $recordsFiltered = $rows->count();
        $totalRecords    = $recordsFiltered;

        // Build final data
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'             => $start + $i + 1,
                'product_name'      => e($r['product_name']),
                'category_name'     => e($r['category_name']),
                'sub_category_name' => e($r['sub_category_name']),
                'branch_name'       => e($r['branch_name']),
                'qty'               => $r['qty'],
                'reorder_level'     => $r['reorder_level'],
                'status'            => $r['status'],
                'nearest_expiry'    => $r['nearest_expiry'] ?? '',
                'action'            => $r['actions'],
            ];
        }

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function expiryPage()
    {
        return view('reports.expired_near_expiry', [
            'branches'      => \App\Models\Branch::select('id', 'name')->orderBy('name')->get(),
            'categories'    => \App\Models\Category::select('id', 'name')->orderBy('name')->get(),
            'subCategories' => \App\Models\SubCategory::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function getExpiryData(Request $request)
    {
        $branchId      = $request->integer('branch_id');
        $categoryId    = $request->integer('category_id');
        $subCategoryId = $request->integer('sub_category_id');
        $type          = $request->string('type')->toString();   // '', 'expired', 'near'
        $daysWindow    = (int) $request->input('days', 30);
        $searchValue   = $request->input('search.value');

        $tz     = config('app.timezone', 'Asia/Kolkata');
        $today  = Carbon::today($tz);
        $nearTo = $today->copy()->addDays(max($daysWindow, 0));

        // ---------- Base (NO SEARCH) for recordsTotal ----------
        $baseNoSearch = DB::table('inventories as inv')
            ->join('products as p', 'p.id', '=', 'inv.product_id')
            ->leftJoin('branches as b', 'b.id', '=', 'inv.store_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('sub_categories as sc', 'sc.id', '=', 'p.subcategory_id')
            ->where('p.is_active', 'yes')
            ->where('p.is_deleted', 'no')
            ->whereNotNull('inv.expiry_date')
            ->when($branchId,      fn($q, $v) => $q->where('inv.store_id', $v))
            ->when($categoryId,    fn($q, $v) => $q->where('p.category_id', $v))
            ->when($subCategoryId, fn($q, $v) => $q->where('p.subcategory_id', $v));

        if ($type === 'expired') {
            $baseNoSearch->where('inv.expiry_date', '<', $today->toDateString());
        } elseif ($type === 'near') {
            $baseNoSearch->whereBetween('inv.expiry_date', [$today->toDateString(), $nearTo->toDateString()]);
        }

        $groupedNoSearch = (clone $baseNoSearch)
            ->selectRaw("
                inv.store_id AS branch_id,
                COALESCE(b.name, CONCAT('Branch #', inv.store_id)) AS branch_name,
                p.id AS product_id,
                p.name AS product_name,
                c.name AS category_name,
                sc.name AS sub_category_name,
                inv.batch_no AS batch_no,
                inv.expiry_date AS expiry_date,
                SUM(inv.quantity) AS qty
            ")
            ->groupBy(
                'inv.store_id',
                'b.name',
                'p.id',
                'p.name',
                'c.name',
                'sc.name',
                'inv.batch_no',
                'inv.expiry_date'
            )
            ->get();

        $totalRecords = $groupedNoSearch->count();

        // ---------- Base WITH SEARCH for recordsFiltered + data ----------
        $base = (clone $baseNoSearch);
        if (!empty($searchValue)) {
            $base->where(function ($q) use ($searchValue) {
                $q->where('p.name', 'like', "%{$searchValue}%")
                    ->orWhere('c.name', 'like', "%{$searchValue}%")
                    ->orWhere('sc.name', 'like', "%{$searchValue}%")
                    ->orWhere('b.name', 'like', "%{$searchValue}%")
                    ->orWhere('inv.batch_no', 'like', "%{$searchValue}%");
            });
        }

        $grouped = $base
            ->selectRaw("
                inv.store_id AS branch_id,
                COALESCE(b.name, CONCAT('Branch #', inv.store_id)) AS branch_name,
                p.id AS product_id,
                p.name AS product_name,
                c.name AS category_name,
                sc.name AS sub_category_name,
                inv.batch_no AS batch_no,
                inv.expiry_date AS expiry_date,
                SUM(inv.quantity) AS qty
            ")
            ->groupBy(
                'inv.store_id',
                'b.name',
                'p.id',
                'p.name',
                'c.name',
                'sc.name',
                'inv.batch_no',
                'inv.expiry_date'
            )
            ->get();

        // ---------- Map rows: compute days & status (RED if expired or <= 10 days) ----------
        $rows = $grouped->map(function ($r) use ($today) {
            $exp   = $r->expiry_date ? Carbon::parse($r->expiry_date) : null;
            $days  = $exp ? $today->diffInDays($exp, false) : null; // negative if past
            $badge = fn($class, $text) => '<span class="badge ' . $class . '">' . $text . '</span>';

            $statusText = 'OK';
            $statusHtml = $badge('bg-secondary', 'OK');

            if ($exp) {
                if ($days < 0) {
                    $statusText = 'Expired (' . abs($days) . ' day' . (abs($days) === 1 ? '' : 's') . ' ago)';
                    $statusHtml = $badge('bg-danger', $statusText); // RED
                } elseif ($days <= 10) {
                    $statusText = 'Near Expiry (' . $days . ' day' . ($days === 1 ? '' : 's') . ')';
                    $statusHtml = $badge('bg-danger', $statusText); // RED
                } else {
                    $statusText = 'OK (' . $days . ' days)';
                    $statusHtml = $badge('bg-secondary', $statusText);
                }
            }

            // Sort key: expired group first by abs(days) asc, then upcoming by days asc
            if ($days === null) {
                $sortKey = '2|99999'; // push null-expiry (shouldn't happen) to bottom
            } elseif ($days < 0) {
                $sortKey = '0|' . str_pad((string)abs($days), 5, '0', STR_PAD_LEFT);
            } else {
                $sortKey = '1|' . str_pad((string)$days, 5, '0', STR_PAD_LEFT);
            }

            return [
                'product_name'       => $r->product_name,
                'category_name'      => $r->category_name,
                'sub_category_name'  => $r->sub_category_name,
                'branch_name'        => $r->branch_name,
                'batch_no'           => $r->batch_no,
                'expiry_date'        => $r->expiry_date,
                'qty'                => (int) $r->qty,
                'status_text'        => $statusText,
                'status_html'        => $statusHtml,  // used in UI
                'days_to_expiry'     => $days,
                'sort_key'           => $sortKey,
                'actions'            => '',
            ];
        })->values();

        $recordsFiltered = $rows->count();

        // ---------- Ordering ----------
        // Map must match the DataTables columns order in the Blade below
        $columns = [
            null,                 // 0 Sr No (not orderable)
            'product_name',       // 1
            'category_name',      // 2
            'sub_category_name',  // 3
            'branch_name',        // 4
            'batch_no',           // 5
            'sort_key',           // 6 (expiry column → smart sort by sort_key)
            'qty',                // 7
            'status_text',        // 8 (keep text for consistent sorting if chosen)
            null,                 // 9 actions
        ];

        if (!empty($request->order)) {
            $orderColumnIndex = (int) $request->order[0]['column'];
            $orderDir = strtolower($request->order[0]['dir'] ?? 'asc');
            $key = $columns[$orderColumnIndex] ?? 'sort_key';
            $rows = $rows->sortBy($key, SORT_NATURAL, $orderDir === 'desc')->values();
        } else {
            // Default: "less day expired first", then upcoming soonest
            $rows = $rows->sortBy('sort_key', SORT_NATURAL)->values();
        }

        // ---------- Pagination ----------
        $start  = (int) $request->start;
        $length = (int) $request->length;
        $paged  = $length > 0 ? $rows->slice($start, $length)->values() : $rows;

        // ---------- Payload ----------
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'             => $start + $i + 1,
                'product_name'      => e($r['product_name']),
                'category_name'     => e($r['category_name']),
                'sub_category_name' => e($r['sub_category_name']),
                'branch_name'       => e($r['branch_name']),
                'batch_no'          => e($r['batch_no'] ?? ''),
                'expiry_date'       => e($r['expiry_date'] ?? ''),
                'qty'               => $r['qty'],
                'status'            => $r['status_html'], // HTML badge (red/grey)
                'action'            => $r['actions'],
            ];
        }

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function profitLossPage()
    {
        return view('reports.profit_loss', [
            'branches' => \App\Models\Branch::select('id', 'name')
                ->where('is_deleted', 'no')->orderBy('name')->get(),
        ]);
    }
    public function getProfitLossData(Request $request)
    {
        $branchId    = $request->integer('branch_id');
        $searchValue = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? \Illuminate\Support\Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : \Illuminate\Support\Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? \Illuminate\Support\Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : \Illuminate\Support\Carbon::now($tz)->endOfMonth();

        /* -------- Products: costs + selling (for margin fallback) -------- */
        $productRows = \Illuminate\Support\Facades\DB::table('products')
            ->select('id', 'cost_price', 'sell_price', 'discount_price', 'discount_amt', 'price_apply_date')
            ->where('is_active', 'yes')->where('is_deleted', 'no')
            ->get();

        $productMap = [];
        foreach ($productRows as $p) {
            $productMap[$p->id] = [
                'cost'       => (float)($p->cost_price ?? 0),
                'sell'       => (float)($p->sell_price ?? 0),
                'disc_price' => isset($p->discount_price) ? (float)$p->discount_price : null,
                'disc_amt'   => isset($p->discount_amt)   ? (float)$p->discount_amt   : 0.0,
                'apply_date' => $p->price_apply_date, // Y-m-d or null
            ];
        }

        /* -------- Invoices (sales) -------- */
        $invoices = \Illuminate\Support\Facades\DB::table('invoices as i')
            ->leftJoin('branches as b', 'b.id', '=', 'i.branch_id')
            ->select(
                'i.id',
                'i.branch_id',
                'b.name as branch_name',
                'i.sub_total',
                'i.tax',
                'i.items',
                'i.commission_amount',
                'i.party_amount',
                'i.status',
                'i.created_at'
            )
            ->where('i.status', '!=', 'Hold')
            ->whereBetween('i.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v))
            ->get();

        // Buckets: key = "Y-m-d|branch_id"
        $buckets = [];

        // Helper: figure out qty, gross, unit for a line (prefer line price; fallback to product sell - product discount)
        $resolveLine = function (array $it, array $prod, string $saleDate) {
            $qty = (float)($it['quantity'] ?? $it['qty'] ?? 0);

            // 1) If line TOTAL exists, trust it
            foreach (['total', 'item_total', 'line_total', 'subtotal'] as $k) {
                if (isset($it[$k]) && is_numeric($it[$k])) {
                    $gross = (float)$it[$k];
                    $unit  = $qty > 0
                        ? $gross / $qty
                        : (float)($it['unit_price'] ?? $it['price'] ?? $it['sell_price'] ?? $it['mrp'] ?? $it['rate'] ?? 0);
                    return [$qty, $gross, $unit];
                }
            }
            // 2) Else if line UNIT exists, use it
            foreach (['unit_price', 'price', 'sell_price', 'mrp', 'rate'] as $k) {
                if (isset($it[$k]) && is_numeric($it[$k])) {
                    $unit  = (float)$it[$k];
                    $gross = $qty * $unit;
                    return [$qty, $gross, $unit];
                }
            }
            // 3) Fallback to product master (sell less product-level discount if active)
            $unit = (float)($prod['sell'] ?? 0);
            $applyOk = empty($prod['apply_date']) || ($saleDate >= $prod['apply_date']);
            if ($applyOk && !empty($prod['disc_price'])) {
                $unit = (float)$prod['disc_price'];
            } elseif ($applyOk && ($prod['disc_amt'] ?? 0) > 0) {
                $unit = max(0.0, $unit - (float)$prod['disc_amt']);
            }
            $gross = $qty * $unit;
            return [$qty, $gross, $unit];
        };

        foreach ($invoices as $inv) {
            $date = \Illuminate\Support\Carbon::parse($inv->created_at, $tz)->toDateString();
            $bid  = (int)($inv->branch_id ?? 0);
            $key  = $date . '|' . $bid;

            if (!isset($buckets[$key])) {
                $buckets[$key] = [
                    'date'         => $date,
                    'branch_id'    => $bid,
                    'branch_name'  => $inv->branch_name ?? ('Branch #' . $bid),
                    'bills'        => 0,
                    'net_sales'    => 0.0, // after discounts
                    'discounts'    => 0.0, // commission+party
                    'tax'          => 0.0,
                    'total_sales'  => 0.0, // net + tax
                    'cogs'         => 0.0,
                    'refunds'      => 0.0,
                    'expenses'     => 0.0,
                ];
            }
            $buckets[$key]['bills'] += 1;

            $items = json_decode($inv->items, true) ?: [];
            if (empty($items)) {
                // still add tax if present
                $buckets[$key]['tax']         += (float)$inv->tax;
                $buckets[$key]['total_sales'] += (float)$inv->tax; // net=0 + tax
                continue;
            }

            // Build per-invoice line list to allocate invoice-level discount proportionally
            $lines = [];
            $invoiceGross = 0.0;

            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                if (!$pid || !isset($productMap[$pid])) continue;

                [$qty, $gross, $unit] = $resolveLine($it, $productMap[$pid], $date);
                if ($qty == 0 && $gross == 0) continue;

                $lines[] = ['pid' => $pid, 'qty' => $qty, 'gross' => $gross];
                $invoiceGross += $gross;
            }

            // Invoice level: discounts & tax
            $invoiceDiscount = max(0, (float)$inv->commission_amount) + max(0, (float)$inv->party_amount);
            $invoiceTax      = (float)$inv->tax;

            // Allocate discounts by gross share; compute net & cogs from product cost
            $sumDisc = 0.0;
            $sumNet  = 0.0;
            $sumCogs = 0.0;

            foreach ($lines as $L) {
                $pid   = $L['pid'];
                $qty   = $L['qty'];
                $gross = $L['gross'];

                $share    = $invoiceGross > 0 ? ($gross / $invoiceGross) : 0.0;
                $discPart = $invoiceDiscount * $share;
                $netLine  = max(0.0, $gross - $discPart);

                $sumDisc += $discPart;
                $sumNet  += $netLine;
                $sumCogs += $qty * ($productMap[$pid]['cost'] ?? 0);
            }

            // Update bucket
            $buckets[$key]['discounts']   += $sumDisc;
            $buckets[$key]['net_sales']   += $sumNet;
            $buckets[$key]['tax']         += $invoiceTax;
            $buckets[$key]['total_sales'] += ($sumNet + $invoiceTax);
            $buckets[$key]['cogs']        += $sumCogs;
        }

        /* -------- Expenses (by date×branch) -------- */
        $expQ = \Illuminate\Support\Facades\DB::table('expenses as e')
            ->select('e.branch_id', 'e.expense_date', \Illuminate\Support\Facades\DB::raw('SUM(e.amount) as amt'))
            ->whereBetween('e.expense_date', [$start->toDateString(), $end->toDateString()])
            ->when($branchId, fn($q, $v) => $q->where('e.branch_id', $v))
            ->groupBy('e.branch_id', 'e.expense_date')
            ->get();

        $branchNameCache = [];
        foreach ($expQ as $ex) {
            $date = $ex->expense_date;
            $bid  = (int)($ex->branch_id ?? 0);
            $key  = $date . '|' . $bid;

            if (!isset($buckets[$key])) {
                if (!array_key_exists($bid, $branchNameCache)) {
                    $branchNameCache[$bid] = \Illuminate\Support\Facades\DB::table('branches')->where('id', $bid)->value('name');
                }
                $buckets[$key] = [
                    'date'         => $date,
                    'branch_id'    => $bid,
                    'branch_name'  => $branchNameCache[$bid] ?? ('Branch #' . $bid),
                    'bills'        => 0,
                    'net_sales'    => 0.0,
                    'discounts'    => 0.0,
                    'tax'          => 0.0,
                    'total_sales'  => 0.0,
                    'cogs'         => 0.0,
                    'refunds'      => 0.0,
                    'expenses'     => 0.0,
                ];
            }
            $buckets[$key]['expenses'] += (float)$ex->amt;
        }

        /* -------- Refunds (ONLY_FULL_GROUP_BY safe) -------- */
        $refQ = \Illuminate\Support\Facades\DB::table('credit_histories as ch')
            ->selectRaw('ch.store_id, DATE(ch.created_at) as tx_date, SUM(CASE WHEN ch.type="debit" THEN ch.debit_amount ELSE 0 END) as refunds')
            ->where('ch.transaction_kind', 'refund')
            ->whereBetween('ch.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('ch.store_id', $v))
            ->groupBy('ch.store_id', \Illuminate\Support\Facades\DB::raw('DATE(ch.created_at)'))
            ->get();

        foreach ($refQ as $rf) {
            $date = $rf->tx_date;
            $bid  = (int)($rf->store_id ?? 0);
            $key  = $date . '|' . $bid;

            if (!isset($buckets[$key])) {
                if (!array_key_exists($bid, $branchNameCache)) {
                    $branchNameCache[$bid] = \Illuminate\Support\Facades\DB::table('branches')->where('id', $bid)->value('name');
                }
                $buckets[$key] = [
                    'date'         => $date,
                    'branch_id'    => $bid,
                    'branch_name'  => $branchNameCache[$bid] ?? ('Branch #' . $bid),
                    'bills'        => 0,
                    'net_sales'    => 0.0,
                    'discounts'    => 0.0,
                    'tax'          => 0.0,
                    'total_sales'  => 0.0,
                    'cogs'         => 0.0,
                    'refunds'      => 0.0,
                    'expenses'     => 0.0,
                ];
            }
            $buckets[$key]['refunds'] += (float)$rf->refunds;
        }

        /* -------- Build rows -------- */
        $rows = collect($buckets)->map(function ($r) {
            $netAfterRefunds = (float)$r['net_sales'] - (float)$r['refunds']; // after discounts then minus refunds
            $grossProfit     = $netAfterRefunds - (float)$r['cogs'];
            $netProfit       = $grossProfit - (float)$r['expenses'];

            return [
                'date'         => $r['date'],
                'branch_name'  => $r['branch_name'],
                'bills'        => (int)$r['bills'],
                'net_sales'    => round($r['net_sales'], 2),
                'discounts'    => round($r['discounts'], 2),
                'tax'          => round($r['tax'], 2),
                'total_sales'  => round($r['total_sales'], 2),
                'refunds'      => round($r['refunds'], 2),
                'cogs'         => round($r['cogs'], 2),
                'gross_profit' => round($grossProfit, 2),
                'expenses'     => round($r['expenses'], 2),
                'net_profit'   => round($netProfit, 2),
            ];
        })->values();

        $totalRecords = $rows->count();

        // Search (date or branch)
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(function ($r) use ($sv) {
                return mb_strpos(mb_strtolower($r['date']), $sv) !== false
                    || mb_strpos(mb_strtolower($r['branch_name']), $sv) !== false;
            })->values();
        }
        $recordsFiltered = $rows->count();

        // Ordering
        $columns = [
            null,            // 0 Sr No
            'date',          // 1
            'branch_name',   // 2
            'bills',         // 3
            'net_sales',     // 4
            'discounts',     // 5
            'tax',           // 6
            'total_sales',   // 7
            'refunds',       // 8
            'cogs',          // 9
            'gross_profit',  // 10
            'expenses',      // 11
            'net_profit',    // 12
            null,            // 13
        ];
        if (!empty($request->order)) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'date';
            if ($key) $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('date', SORT_NATURAL, true)->values(); // date desc
        }

        // Pagination
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // DataTables payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'        => $startIdx + $i + 1,
                'date'         => e($r['date']),
                'branch_name'  => e($r['branch_name']),
                'bills'        => $r['bills'],
                'net_sales'    => number_format($r['net_sales'], 2),
                'discounts'    => number_format($r['discounts'], 2),
                'tax'          => number_format($r['tax'], 2),
                'total_sales'  => number_format($r['total_sales'], 2),
                'refunds'      => number_format($r['refunds'], 2),
                'cogs'         => number_format($r['cogs'], 2),
                'gross_profit' => number_format($r['gross_profit'], 2),
                'expenses'     => number_format($r['expenses'], 2),
                'net_profit'   => number_format($r['net_profit'], 2),
                'action'       => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function productPLPage()
    {
        return view('reports.product_pl', [
            'branches'      => \App\Models\Branch::select('id', 'name')->where('is_deleted', 'no')->orderBy('name')->get(),
            'categories'    => \App\Models\Category::select('id', 'name')->where('is_deleted', 'no')->orderBy('name')->get(),
            'subCategories' => \App\Models\SubCategory::select('id', 'name')->where('is_deleted', 'no')->orderBy('name')->get(),
        ]);
    }

    public function getProductPLData(Request $request)
    {
        $branchId      = $request->integer('branch_id');
        $categoryId    = $request->integer('category_id');
        $subCategoryId = $request->integer('sub_category_id');
        $productId     = $request->integer('product_id');
        $searchValue   = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? \Illuminate\Support\Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : \Illuminate\Support\Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? \Illuminate\Support\Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : \Illuminate\Support\Carbon::now($tz)->endOfMonth();

        // Products map
        $productRows = \Illuminate\Support\Facades\DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('sub_categories as sc', 'sc.id', '=', 'p.subcategory_id')
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                'p.cost_price',
                'p.sell_price',
                'p.category_id',
                'p.subcategory_id',
                'c.name as category_name',
                'sc.name as sub_category_name'
            )
            ->where('p.is_active', 'yes')->where('p.is_deleted', 'no')
            ->get();

        $productMap = [];
        foreach ($productRows as $row) {
            $productMap[$row->id] = [
                'name'              => $row->name,
                'sku'               => $row->sku,
                'cost'              => (float)($row->cost_price ?? 0),
                'sell'              => (float)($row->sell_price ?? 0),
                'category_id'       => $row->category_id,
                'subcategory_id'    => $row->subcategory_id,
                'category_name'     => $row->category_name,
                'sub_category_name' => $row->sub_category_name,
            ];
        }

        // Invoices
        $invoices = \Illuminate\Support\Facades\DB::table('invoices as i')
            ->select(
                'i.id',
                'i.branch_id',
                'i.items',
                'i.sub_total',
                'i.tax',
                'i.commission_amount',
                'i.party_amount',
                'i.status',
                'i.created_at'
            )
            ->where('i.status', '!=', 'Hold')
            ->whereBetween('i.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v))
            ->get();

        $perProduct = [];
        $revByBranchDate = [];        // branch|date => gross base
        $revByBranchDateProduct = []; // branch|date|product => gross base

        // STRICT resolver: DO NOT read total/subtotal/mrp/rate
        $resolveLine = function (array $it, array $prod) {
            $qty = (float)($it['quantity'] ?? $it['qty'] ?? $it['qnt'] ?? 0);

            // 1) Prefer explicit unit price on the line (NO mrp/rate)
            foreach (['sell_price'] as $k) {
                if (isset($it[$k]) && is_numeric($it[$k])) {
                    $unit = (float)$it[$k];
                    return [$qty, $qty * $unit];
                }
            }
            // 2) Fallback to product master sell_price
            $unit = (float)($prod['sell'] ?? 0);
            return [$qty, $qty * $unit];
        };

        foreach ($invoices as $inv) {
            $date   = \Illuminate\Support\Carbon::parse($inv->created_at, $tz)->toDateString();
            $branch = (int)($inv->branch_id ?? 0);
            $items  = json_decode($inv->items, true) ?: [];

            $lines = [];
            $invoiceGross = 0.0;

            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                if (!$pid || !isset($productMap[$pid])) continue;

                [$qty, $gross] = $resolveLine($it, $productMap[$pid]);
                if ($qty == 0 && $gross == 0) continue;

                $lines[] = ['product_id' => $pid, 'qty' => $qty, 'gross' => $gross];
                $invoiceGross += $gross;

                $revByBranchDate["$branch|$date"] = ($revByBranchDate["$branch|$date"] ?? 0) + $gross;
                $revByBranchDateProduct["$branch|$date|$pid"] = ($revByBranchDateProduct["$branch|$date|$pid"] ?? 0) + $gross;
            }

            if (empty($lines)) continue;

            $invoiceDiscount = max(0, (float)$inv->commission_amount) + max(0, (float)$inv->party_amount);
            $invoiceTax      = (float)$inv->tax;

            foreach ($lines as $L) {
                $pid   = $L['product_id'];
                $qty   = $L['qty'];
                $gross = $L['gross'];
                $share = $invoiceGross > 0 ? ($gross / $invoiceGross) : 0.0;

                $discAlloc = $invoiceDiscount * $share;
                $taxAlloc  = $invoiceTax * $share;
                $netSales  = max(0.0, $gross - $discAlloc);

                if (!isset($perProduct[$pid])) {
                    $perProduct[$pid] = [
                        'qty'            => 0.0,
                        'gross_revenue'  => 0.0,
                        'discounts'      => 0.0,
                        'net_sales'      => 0.0,
                        'tax'            => 0.0,
                        'total_sales'    => 0.0,
                        'cogs'           => 0.0,
                        'refunds'        => 0.0,
                    ];
                }

                $perProduct[$pid]['qty']           += $qty;
                $perProduct[$pid]['gross_revenue'] += $gross; // ✅ now based on line price or product sell_price only
                $perProduct[$pid]['discounts']     += $discAlloc;
                $perProduct[$pid]['net_sales']     += $netSales;
                $perProduct[$pid]['tax']           += $taxAlloc;
                $perProduct[$pid]['total_sales']   += $netSales + $taxAlloc;
                $perProduct[$pid]['cogs']          += $qty * ($productMap[$pid]['cost'] ?? 0);
            }
        }

        // Refund allocation by branch×date share
        $refQ = \Illuminate\Support\Facades\DB::table('credit_histories as ch')
            ->selectRaw('ch.store_id, DATE(ch.created_at) as tx_date, SUM(CASE WHEN ch.type="debit" THEN ch.debit_amount ELSE 0 END) as refunds')
            ->where('ch.transaction_kind', 'refund')
            ->whereBetween('ch.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('ch.store_id', $v))
            ->groupBy('ch.store_id', \Illuminate\Support\Facades\DB::raw('DATE(ch.created_at)'))
            ->get();

        foreach ($refQ as $rf) {
            $branch = (int)($rf->store_id ?? 0);
            $date   = $rf->tx_date;
            $total  = (float)$rf->refunds;
            $base   = $revByBranchDate["$branch|$date"] ?? 0.0;
            if ($base <= 0) continue;

            foreach ($revByBranchDateProduct as $key => $prodRev) {
                [$b, $d, $pid] = explode('|', $key);
                if ((int)$b !== $branch || $d !== $date) continue;
                $share = $prodRev / $base;
                if (isset($perProduct[(int)$pid])) {
                    $perProduct[(int)$pid]['refunds'] += $total * $share;
                }
            }
        }

        // Build rows
        $rows = collect($perProduct)->map(function ($m, $pid) use ($productMap) {
            $grossProfit = ((float)$m['net_sales'] - (float)$m['refunds']) - (float)$m['cogs'];
            $p = $productMap[$pid] ?? ['name' => "#{$pid}", 'sku' => '', 'category_name' => '', 'sub_category_name' => '', 'category_id' => null, 'subcategory_id' => null];

            return [
                'product_id'        => (int)$pid,
                'product_name'      => $p['name'],
                'sku'               => $p['sku'],
                'category_id'       => $p['category_id'],
                'subcategory_id'    => $p['subcategory_id'],
                'category_name'     => $p['category_name'],
                'sub_category_name' => $p['sub_category_name'],
                'qty'               => round($m['qty'], 2),
                'gross_revenue'     => round($m['gross_revenue'], 2),
                'discounts'         => round($m['discounts'], 2),
                'net_sales'         => round($m['net_sales'], 2),
                'tax'               => round($m['tax'], 2),
                'total_sales'       => round($m['total_sales'], 2),
                'cogs'              => round($m['cogs'], 2),
                'refunds'           => round($m['refunds'], 2),
                'gross_profit'      => round($grossProfit, 2),
                'net_profit'        => round($grossProfit, 2),
            ];
        })->values();

        // Filters
        if ($categoryId)    $rows = $rows->where('category_id', $categoryId)->values();
        if ($subCategoryId) $rows = $rows->where('subcategory_id', $subCategoryId)->values();
        if ($productId)     $rows = $rows->where('product_id', $productId)->values();

        // Search
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(function ($r) use ($sv) {
                return str_contains(mb_strtolower($r['product_name']), $sv)
                    || str_contains(mb_strtolower($r['sku']), $sv)
                    || str_contains(mb_strtolower($r['category_name'] ?? ''), $sv)
                    || str_contains(mb_strtolower($r['sub_category_name'] ?? ''), $sv);
            })->values();
        }

        $totalRecords    = $rows->count();
        $recordsFiltered = $totalRecords;

        // Order (default by net_profit desc)
        $columns = [
            null,
            'product_name',
            'category_name',
            'sub_category_name',
            'qty',
            'gross_revenue',
            'discounts',
            'net_sales',
            'tax',
            'total_sales',
            'cogs',
            'refunds',
            'gross_profit',
            'net_profit',
            null
        ];
        if (!empty($request->order)) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'net_profit';
            if ($key) $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            $rows = $rows->sortBy('net_profit', SORT_NATURAL, true)->values();
        }

        // Paginate & payload
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'             => $startIdx + $i + 1,
                'product_name'      => e($r['product_name']),
                'category_name'     => e($r['category_name'] ?? ''),
                'sub_category_name' => e($r['sub_category_name'] ?? ''),
                'qty'               => number_format($r['qty'], 2),
                'gross_revenue'     => number_format($r['gross_revenue'], 2),
                'discounts'         => number_format($r['discounts'], 2),
                'net_sales'         => number_format($r['net_sales'], 2),
                'tax'               => number_format($r['tax'], 2),
                'total_sales'       => number_format($r['total_sales'], 2),
                'cogs'              => number_format($r['cogs'], 2),
                'refunds'           => number_format($r['refunds'], 2),
                'gross_profit'      => number_format($r['gross_profit'], 2),
                'net_profit'        => number_format($r['net_profit'], 2),
                'action'            => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function dailyCashPage()
    {
        return view('reports.daily_cash', [
            'branches' => \App\Models\Branch::select('id', 'name')
                ->where('is_deleted', 'no')->orderBy('name')->get(),
        ]);
    }

    public function getDailyCashData(Request $request)
    {
        $branchId    = $request->integer('branch_id');
        $status      = $request->string('status')->toString(); // '', 'pending','completed','cancelled','closing'
        $searchValue = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        // -------- Base (with filters; no search yet) --------
        $base = DB::table('shift_closings as sc')
            ->leftJoin('branches as b', 'b.id', '=', 'sc.branch_id')
            ->whereBetween(DB::raw('COALESCE(sc.closing_shift_time, sc.created_at)'), [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('sc.branch_id', $v))
            ->when($status !== '', fn($q) => $q->where('sc.status', request('status')));

        // -------- Grouped result: Day × Branch --------
        $grouped = (clone $base)
            ->selectRaw("
                DATE(COALESCE(sc.closing_shift_time, sc.created_at)) AS work_date,
                sc.branch_id,
                COALESCE(b.name, CONCAT('Branch #', sc.branch_id)) AS branch_name,
                COUNT(*)                         AS shifts_count,
                COALESCE(SUM(sc.opening_cash),0)            AS opening_cash_sum,
                COALESCE(SUM(sc.cash_added),0)              AS cash_added_sum,
                COALESCE(SUM(sc.upi_payment),0)             AS upi_sum,
                COALESCE(SUM(sc.withdrawal_payment),0)      AS withdrawal_sum,
                COALESCE(SUM(sc.closing_cash),0)            AS closing_cash_sum,
                COALESCE(SUM(sc.cash_discrepancy),0)        AS discrepancy_sum,
                COALESCE(SUM(sc.deshi_sales),0)             AS deshi_sum,
                COALESCE(SUM(sc.beer_sales),0)              AS beer_sum,
                COALESCE(SUM(sc.english_sales),0)           AS english_sum,
                COALESCE(SUM(sc.discount),0)                AS discount_sum
            ")
            ->groupBy(
                DB::raw('DATE(COALESCE(sc.closing_shift_time, sc.created_at))'),
                'sc.branch_id',
                'b.name'
            )
            ->get();

        // Build rows (compute total_sales)
        $rows = $grouped->map(function ($r) {
            $totalSales = (float)$r->deshi_sum + (float)$r->beer_sum + (float)$r->english_sum - (float)$r->discount_sum;

            return [
                'work_date'        => $r->work_date,
                'branch_name'      => $r->branch_name,
                'shifts_count'     => (int)$r->shifts_count,
                'opening_cash'     => (float)$r->opening_cash_sum,
                'cash_added'       => (float)$r->cash_added_sum,
                'upi_payment'      => (float)$r->upi_sum,
                'withdrawal'       => (float)$r->withdrawal_sum,
                'closing_cash'     => (float)$r->closing_cash_sum,
                'discrepancy'      => (float)$r->discrepancy_sum,
                'deshi_sales'      => (float)$r->deshi_sum,
                'beer_sales'       => (float)$r->beer_sum,
                'english_sales'    => (float)$r->english_sum,
                'discount'         => (float)$r->discount_sum,
                'total_sales'      => round($totalSales, 2),
            ];
        })->values();

        $totalRecords = $rows->count();

        // -------- Search (by date or branch) --------
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(function ($r) use ($sv) {
                return mb_strpos(mb_strtolower($r['work_date']), $sv) !== false
                    || mb_strpos(mb_strtolower($r['branch_name']), $sv) !== false;
            })->values();
        }
        $recordsFiltered = $rows->count();

        // -------- Ordering --------
        $columns = [
            null,            // 0 Sr No
            'work_date',     // 1
            'branch_name',   // 2
            'shifts_count',  // 3
            'opening_cash',  // 4
            'cash_added',    // 5
            'upi_payment',   // 6
            'withdrawal',    // 7
            'closing_cash',  // 8
            'discrepancy',   // 9
            'deshi_sales',   // 10
            'beer_sales',    // 11
            'english_sales', // 12
            'discount',      // 13
            'total_sales',   // 14
            null,            // 15 Actions
        ];
        if (!empty($request->order)) {
            $idx = (int) $request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'work_date';
            if ($key) {
                $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
            }
        } else {
            // Default: latest day first
            $rows = $rows->sortBy('work_date', SORT_NATURAL, true)->values();
        }

        // -------- Pagination --------
        $startIdx = (int) $request->start;
        $length   = (int) $request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // -------- Build DataTables payload --------
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'          => $startIdx + $i + 1,
                'work_date'      => e($r['work_date']),
                'branch_name'    => e($r['branch_name']),
                'shifts_count'   => $r['shifts_count'],
                'opening_cash'   => number_format($r['opening_cash'], 2),
                'cash_added'     => number_format($r['cash_added'], 2),
                'upi_payment'    => number_format($r['upi_payment'], 2),
                'withdrawal'     => number_format($r['withdrawal'], 2),
                'closing_cash'   => number_format($r['closing_cash'], 2),
                'discrepancy'    => number_format($r['discrepancy'], 2),
                'deshi_sales'    => number_format($r['deshi_sales'], 2),
                'beer_sales'     => number_format($r['beer_sales'], 2),
                'english_sales'  => number_format($r['english_sales'], 2),
                'discount'       => number_format($r['discount'], 2),
                'total_sales'    => number_format($r['total_sales'], 2),
                'action'         => '', // add buttons if you like
            ];
        }

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function creditPaymentsPage()
    {
        return view('reports.credit_payments', [
            'branches' => \App\Models\Branch::select('id', 'name')
                ->where('is_deleted', 'no')->orderBy('name')->get(),
            'parties'  => DB::table('party_users')
                ->select('id', 'first_name', 'last_name')
                ->where('is_delete', 'No')
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function getCreditPaymentsData(Request $request)
    {
        $branchId      = $request->integer('branch_id');      // maps to credit_histories.store_id
        $partyUserId   = $request->integer('party_user_id');
        $status        = $request->string('status')->toString();            // paid | unpaid | partial_paid | ''
        $type          = $request->string('type')->toString();              // credit | debit | ''
        $kind          = $request->string('transaction_kind')->toString();  // order | refund | collact_credit | ''
        $searchValue   = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        // ---------- Base query (filters, no search yet) ----------
        $base = DB::table('credit_histories as ch')
            ->leftJoin('party_users as pu', 'pu.id', '=', 'ch.party_user_id')
            ->leftJoin('branches as b', 'b.id', '=', 'ch.store_id')
            ->leftJoin('invoices as i', 'i.id', '=', 'ch.invoice_id')
            ->whereBetween('ch.created_at', [$start, $end])
            ->when($branchId,    fn($q, $v) => $q->where('ch.store_id', $v))
            ->when($partyUserId, fn($q, $v) => $q->where('ch.party_user_id', $v))
            ->when($status !== '', fn($q) => $q->where('ch.status', request('status')))
            ->when($type   !== '', fn($q) => $q->where('ch.type', request('type')))
            ->when($kind   !== '', fn($q) => $q->where('ch.transaction_kind', request('transaction_kind')));

        // total before search
        $totalRecords = (clone $base)->count();

        // ---------- Apply search ----------
        if (!empty($searchValue)) {
            $sv = "%{$searchValue}%";
            $base->where(function ($q) use ($sv) {
                $q->where('i.invoice_number', 'like', $sv)
                    ->orWhere('b.name', 'like', $sv)
                    ->orWhere('pu.first_name', 'like', $sv)
                    ->orWhere('pu.last_name', 'like', $sv)
                    ->orWhere('pu.mobile_number', 'like', $sv)
                    ->orWhere('ch.type', 'like', $sv)
                    ->orWhere('ch.transaction_kind', 'like', $sv)
                    ->orWhere('ch.status', 'like', $sv);
            });
        }

        $filteredRecords = (clone $base)->count();

        // ---------- Ordering ----------
        // Must match DataTables columns below
        $columns = [
            'ch.id',                         // 0 Sr No (we'll ignore)
            DB::raw('DATE(ch.created_at)'),  // 1 Date
            'b.name',                        // 2 Branch
            DB::raw("CONCAT(COALESCE(pu.first_name,''),' ',COALESCE(pu.last_name,''))"), // 3 Party
            'i.invoice_number',              // 4 Invoice
            'ch.type',                       // 5 Type
            'ch.transaction_kind',           // 6 Kind
            'ch.total_amount',               // 7 Total Amount
            'ch.credit_amount',              // 8 Credit Amt
            'ch.debit_amount',               // 9 Debit Amt
            DB::raw('(ch.credit_amount - ch.debit_amount)'), // 10 Net
            'ch.status',                     // 11 Status
            'ch.created_by',                 // 12 Created By
        ];

        if ($request->order) {
            $orderColumnIndex = (int)$request->order[0]['column'];
            $orderDir = strtolower($request->order[0]['dir'] ?? 'desc');
            $orderCol = $columns[$orderColumnIndex] ?? DB::raw('DATE(ch.created_at)');
            $base->orderBy($orderCol, $orderDir);
        } else {
            $base->orderBy('ch.created_at', 'desc');
        }

        // ---------- Pagination ----------
        if ($request->length > 0) {
            $base->skip((int)$request->start)->take((int)$request->length);
        }

        // ---------- Fetch rows ----------
        $rows = $base->selectRaw("
                ch.id,
                ch.created_at,
                ch.store_id,
                COALESCE(b.name, CONCAT('Branch #', ch.store_id)) AS branch_name,
                ch.party_user_id,
                TRIM(CONCAT(COALESCE(pu.first_name,''),' ',COALESCE(pu.last_name,''))) AS party_name,
                pu.mobile_number,
                ch.invoice_id,
                i.invoice_number,
                ch.type,
                ch.transaction_kind,
                ch.total_amount,
                ch.credit_amount,
                ch.debit_amount,
                (ch.credit_amount - ch.debit_amount) AS net_amount,
                ch.status,
                ch.created_by
            ")
            ->get();

        // ---------- Build DataTables payload ----------
        $data = [];
        $startIndex = (int)$request->start;
        foreach ($rows as $idx => $r) {
            $typeBadge = $r->type === 'debit'
                ? '<span class="badge bg-success">debit</span>'
                : '<span class="badge bg-warning text-dark">credit</span>';

            $kindTxt = e($r->transaction_kind); // order | refund | collact_credit
            $statusBadge = match ($r->status) {
                'paid'         => '<span class="badge bg-success">paid</span>',
                'partial_paid' => '<span class="badge bg-info text-dark">partial</span>',
                'unpaid'       => '<span class="badge bg-danger">unpaid</span>',
                default        => '<span class="badge bg-secondary">' . e($r->status) . '</span>',
            };

            $invoiceLink = $r->invoice_number
                ? '<a href="' . url('/view-invoice/' . $r->invoice_id) . '" class="badge bg-primary">' . $r->invoice_number . '</a>'
                : '';

            $data[] = [
                'sr_no'          => $startIndex + $idx + 1,
                'tx_date'        => Carbon::parse($r->created_at, $tz)->toDateString(),
                'branch_name'    => e($r->branch_name),
                'party_name'     => e($r->party_name ?: 'N/A'),
                'invoice'        => $invoiceLink,
                'type'           => $typeBadge,
                'kind'           => e($kindTxt),
                'total_amount'   => number_format((float)$r->total_amount, 2),
                'credit_amount'  => number_format((float)$r->credit_amount, 2),
                'debit_amount'   => number_format((float)$r->debit_amount, 2),
                'net_amount'     => number_format((float)$r->net_amount, 2),
                'status'         => $statusBadge,
                'created_by'     => (string)$r->created_by,
                'action'         => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
    }

    public function categorySalesPage()
    {
        return view('reports.category_sales', [
            'branches'      => \App\Models\Branch::select('id', 'name')->where('is_deleted', 'no')->orderBy('name')->get(),
            'categories'    => \App\Models\Category::select('id', 'name')->where('is_deleted', 'no')->orderBy('name')->get(),
            'subCategories' => \App\Models\SubCategory::select('id', 'name', 'category_id')->where('is_deleted', 'no')->orderBy('name')->get(),
        ]);
    }

    public function getCategorySalesData(Request $request)
    {
        $branchId      = $request->integer('branch_id');
        $categoryId    = $request->integer('category_id');     // optional: pre-filter products by category
        $subCategoryId = $request->integer('sub_category_id'); // optional: pre-filter products by subcategory
        $groupBy       = $request->input('group_by', 'category'); // 'category' | 'subcategory'
        $searchValue   = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        /* ------------ Preload product → category/subcategory ------------ */
        $productRows = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('sub_categories as sc', 'sc.id', '=', 'p.subcategory_id')
            ->select(
                'p.id',
                'p.name',
                'p.sku',
                'p.category_id',
                'p.subcategory_id',
                'c.name as category_name',
                'sc.name as sub_category_name'
            )
            ->where('p.is_active', 'yes')->where('p.is_deleted', 'no')
            ->when($categoryId, fn($q, $v) => $q->where('p.category_id', $v))
            ->when($subCategoryId, fn($q, $v) => $q->where('p.subcategory_id', $v))
            ->get();

        $productMap = [];
        foreach ($productRows as $r) {
            $productMap[$r->id] = [
                'category_id'       => $r->category_id,
                'subcategory_id'    => $r->subcategory_id,
                'category_name'     => $r->category_name ?: 'Uncategorized',
                'sub_category_name' => $r->sub_category_name ?: 'Uncategorized',
            ];
        }
        if (empty($productMap)) {
            return response()->json([
                'draw' => (int) $request->draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        /* ------------ Pull invoices ------------ */
        $invoices = DB::table('invoices as i')
            ->select(
                'i.id',
                'i.branch_id',
                'i.items',
                'i.sub_total',
                'i.tax',
                'i.commission_amount',
                'i.party_amount',
                'i.status',
                'i.created_at'
            )
            ->where('i.status', '!=', 'Hold')
            ->whereBetween('i.created_at', [$start, $end])
            ->when($branchId, fn($q, $v) => $q->where('i.branch_id', $v))
            ->get();

        // Helper: compute qty & gross from JSON line
        $computeLineGross = function (array $it) {
            $qty = (float)($it['quantity'] ?? $it['qty'] ?? 0);
            $lineTotal = null;
            foreach (['total', 'item_total', 'line_total', 'subtotal'] as $k) {
                if (isset($it[$k]) && is_numeric($it[$k])) {
                    $lineTotal = (float)$it[$k];
                    break;
                }
            }
            if ($lineTotal === null) {
                $unit = null;
                foreach (['price', 'sell_price', 'unit_price', 'mrp', 'rate'] as $k) {
                    if (isset($it[$k]) && is_numeric($it[$k])) {
                        $unit = (float)$it[$k];
                        break;
                    }
                }
                if ($unit === null) $unit = 0.0;
                $lineTotal = $qty * $unit;
            }
            return [$qty, (float)$lineTotal];
        };

        // Accumulators keyed by group
        // groupKey e.g. "cat:5" or "sub:12"
        $groups = []; // metrics
        $groupBills = []; // groupKey => [invoice_id => 1]

        foreach ($invoices as $inv) {
            $items = json_decode($inv->items, true) ?: [];
            if (empty($items)) continue;

            // Build lines & invoice gross for proportional allocation
            $lines = [];
            $invoiceGross = 0.0;

            foreach ($items as $it) {
                $pid = (int)($it['product_id'] ?? 0);
                if (!$pid || !isset($productMap[$pid])) continue;

                [$qty, $gross] = $computeLineGross($it);
                if ($qty <= 0 && $gross <= 0) continue;

                // optional: narrow by explicit category/subcategory filter
                if ($categoryId && $productMap[$pid]['category_id'] != $categoryId) continue;
                if ($subCategoryId && $productMap[$pid]['subcategory_id'] != $subCategoryId) continue;

                // Group id/name for this product
                $catId   = (int) ($productMap[$pid]['category_id'] ?? 0);
                $subId   = (int) ($productMap[$pid]['subcategory_id'] ?? 0);
                $catName = $productMap[$pid]['category_name'] ?? 'Uncategorized';
                $subName = $productMap[$pid]['sub_category_name'] ?? 'Uncategorized';

                $groupId   = $groupBy === 'subcategory' ? $subId   : $catId;
                $groupName = $groupBy === 'subcategory' ? $subName : $catName;
                $groupKey  = ($groupBy === 'subcategory' ? 'sub:' : 'cat:') . $groupId;

                $lines[] = compact('groupId', 'groupName', 'groupKey', 'qty', 'gross');
                $invoiceGross += $gross;
            }

            if (empty($lines)) continue;

            $invoiceDiscount = max(0, (float)$inv->commission_amount) + max(0, (float)$inv->party_amount);
            $invoiceTax      = (float)$inv->tax;

            foreach ($lines as $L) {
                $share    = $invoiceGross > 0 ? ($L['gross'] / $invoiceGross) : 0.0;
                $disc     = $invoiceDiscount * $share;
                $tax      = $invoiceTax * $share;
                $netSales = max(0.0, $L['gross'] - $disc);
                $total    = $netSales + $tax;

                if (!isset($groups[$L['groupKey']])) {
                    $groups[$L['groupKey']] = [
                        'group_id'   => $L['groupId'],
                        'group_name' => $L['groupName'] ?: 'Uncategorized',
                        'qty'        => 0.0,
                        'gross'      => 0.0,
                        'discounts'  => 0.0,
                        'net_sales'  => 0.0,
                        'tax'        => 0.0,
                        'total'      => 0.0,
                    ];
                    $groupBills[$L['groupKey']] = [];
                }

                $groups[$L['groupKey']]['qty']       += $L['qty'];
                $groups[$L['groupKey']]['gross']     += $L['gross'];
                $groups[$L['groupKey']]['discounts'] += $disc;
                $groups[$L['groupKey']]['net_sales'] += $netSales;
                $groups[$L['groupKey']]['tax']       += $tax;
                $groups[$L['groupKey']]['total']     += $total;

                $groupBills[$L['groupKey']][$inv->id] = 1; // record invoice for "Bills" count
            }
        }

        // Build rows
        $rows = collect($groups)->map(function ($m, $key) use ($groupBills) {
            return [
                'group_name'  => $m['group_name'],
                'qty'         => round($m['qty'], 2),
                'gross'       => round($m['gross'], 2),
                'discounts'   => round($m['discounts'], 2),
                'net_sales'   => round($m['net_sales'], 2),
                'tax'         => round($m['tax'], 2),
                'total'       => round($m['total'], 2),
                'bills'       => count($groupBills[$key] ?? []),
            ];
        })->values();

        $totalRecords = $rows->count();

        // Search (by group name)
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $rows = $rows->filter(fn($r) => mb_strpos(mb_strtolower($r['group_name']), $sv) !== false)->values();
        }
        $recordsFiltered = $rows->count();

        // Ordering
        $columns = [
            null,          // 0 Sr No
            'group_name',  // 1
            'qty',         // 2
            'gross',       // 3
            'discounts',   // 4
            'net_sales',   // 5
            'tax',         // 6
            'total',       // 7
            'bills',       // 8
            null,          // 9 Action
        ];
        if (!empty($request->order)) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'total';
            if ($key) $rows = $rows->sortBy($key, SORT_NATURAL, $dir === 'desc')->values();
        } else {
            // default: Total Sales desc
            $rows = $rows->sortBy('total', SORT_NATURAL, true)->values();
        }

        // Pagination
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? $rows->slice($startIdx, $length)->values() : $rows;

        // DataTables payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'      => $startIdx + $i + 1,
                'group_name' => e($r['group_name']),
                'qty'        => number_format($r['qty'], 2),
                'gross'      => number_format($r['gross'], 2),
                'discounts'  => number_format($r['discounts'], 2),
                'net_sales'  => number_format($r['net_sales'], 2),
                'tax'        => number_format($r['tax'], 2),
                'total'      => number_format($r['total'], 2),
                'bills'      => (int)$r['bills'],
                'action'     => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function discountOfferPage()
    {
        return view('reports.discounts', [
            'branches' => \App\Models\Branch::select('id', 'name')
                ->where('is_deleted', 'no')->orderBy('name')->get(),
            'parties'  => DB::table('party_users')
                ->select('id', 'first_name', 'last_name')
                ->where('is_delete', 'No')
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function getDiscountOfferData(Request $request)
    {
        $branchId      = $request->integer('branch_id');
        $partyUserId   = $request->integer('party_user_id');
        $paymentMode   = $request->string('payment_mode')->toString(); // '', cash|upi|online|credit etc.
        $minDiscPct    = (float) $request->input('min_discount_pct', 0);
        $searchValue   = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        // ---------- Base query (filters; no search yet) ----------
        $base = DB::table('invoices as i')
            ->leftJoin('branches as b', 'b.id', '=', 'i.branch_id')
            ->leftJoin('party_users as pu', 'pu.id', '=', 'i.party_user_id')
            ->where('i.status', '!=', 'Hold')
            ->whereBetween('i.created_at', [$start, $end])
            ->when($branchId,    fn($q, $v) => $q->where('i.branch_id', $v))
            ->when($partyUserId, fn($q, $v) => $q->where('i.party_user_id', $v))
            ->when($paymentMode !== '', fn($q) => $q->where('i.payment_mode', request('payment_mode')));

        // Count before search
        $totalRecords = (clone $base)->count();

        // ---------- Apply search ----------
        if (!empty($searchValue)) {
            $sv = "%{$searchValue}%";
            $base->where(function ($q) use ($sv) {
                $q->where('i.invoice_number', 'like', $sv)
                    ->orWhere('b.name', 'like', $sv)
                    ->orWhere('pu.first_name', 'like', $sv)
                    ->orWhere('pu.last_name', 'like', $sv)
                    ->orWhere('pu.mobile_number', 'like', $sv)
                    ->orWhere('i.payment_mode', 'like', $sv);
            });
        }

        // ---------- Ordering (must match Blade columns) ----------
        $columns = [
            'i.id',                        // 0 Sr No (ignored)
            DB::raw('DATE(i.created_at)'), // 1 Date
            'i.invoice_number',            // 2 Invoice
            'b.name',                      // 3 Branch
            DB::raw("CONCAT(COALESCE(pu.first_name,''),' ',COALESCE(pu.last_name,''))"), // 4 Party
            'i.sub_total',                 // 5 Subtotal (gross)
            'i.commission_amount',         // 6 Commission discount
            'i.party_amount',              // 7 Party discount
            DB::raw('(i.commission_amount + i.party_amount)'), // 8 Total discount
            // 9 Discount % (computed later; order by total_discount/sub_total safely)
            DB::raw('(i.sub_total - (i.commission_amount + i.party_amount))'), // 10 Net before tax
            'i.tax',                       // 11 Tax
            DB::raw('(i.sub_total - (i.commission_amount + i.party_amount) + i.tax)'), // 12 Computed total
            'i.payment_mode',              // 13 Payment Mode
            'i.status',                    // 14 Status
        ];

        // Filter by min discount % (server-side) BEFORE fetching rows
        if ($minDiscPct > 0) {
            $base->whereRaw('(i.commission_amount + i.party_amount) / NULLIF(i.sub_total,0) * 100 >= ?', [$minDiscPct]);
        }

        $filteredRecords = (clone $base)->count();

        if ($request->order) {
            $colIdx = (int)$request->order[0]['column'];
            $dir    = strtolower($request->order[0]['dir'] ?? 'desc');
            if ($colIdx === 9) {
                // Discount % ordering — use expression
                $base->orderByRaw('(i.commission_amount + i.party_amount) / NULLIF(i.sub_total,0) ' . $dir);
            } else {
                $orderCol = $columns[$colIdx] ?? DB::raw('DATE(i.created_at)');
                $base->orderBy($orderCol, $dir);
            }
        } else {
            $base->orderBy('i.created_at', 'desc'); // newest first
        }

        // ---------- Pagination ----------
        if ($request->length > 0) {
            $base->skip((int)$request->start)->take((int)$request->length);
        }

        // ---------- Fetch ----------
        $rows = $base->selectRaw("
                i.id, i.created_at, i.invoice_number,
                i.branch_id, COALESCE(b.name, CONCAT('Branch #', i.branch_id)) as branch_name,
                i.party_user_id,
                TRIM(CONCAT(COALESCE(pu.first_name,''),' ',COALESCE(pu.last_name,''))) AS party_name,
                i.sub_total, i.tax,
                i.commission_amount, i.party_amount,
                i.total, i.payment_mode, i.status
            ")
            ->get();

        // ---------- Build DataTables payload ----------
        $data = [];
        $startIndex = (int)$request->start;

        foreach ($rows as $idx => $r) {
            $subtotal   = (float)$r->sub_total;
            $commDisc   = max(0, (float)$r->commission_amount);
            $partyDisc  = max(0, (float)$r->party_amount);
            $totDisc    = $commDisc + $partyDisc;

            $discPct = ($subtotal > 0) ? round(($totDisc / $subtotal) * 100, 2) : 0.00;

            $netBT   = max(0.0, $subtotal - $totDisc);
            $tax     = (float)$r->tax;
            $compTot = $netBT + $tax;

            $invoiceLink = '<a href="' . url('/view-invoice/' . $r->id) . '" class="badge bg-primary">' . $r->invoice_number . '</a>';

            $data[] = [
                'sr_no'           => $startIndex + $idx + 1,
                'date'            => Carbon::parse($r->created_at, $tz)->toDateString(),
                'invoice'         => $invoiceLink,
                'branch_name'     => e($r->branch_name),
                'party_name'      => e($r->party_name ?: 'N/A'),
                'sub_total'       => number_format($subtotal, 2),
                'commission_disc' => number_format($commDisc, 2),
                'party_disc'      => number_format($partyDisc, 2),
                'total_disc'      => number_format($totDisc, 2),
                'discount_pct'    => number_format($discPct, 2) . ' %',
                'net_before_tax'  => number_format($netBT, 2),
                'tax'             => number_format($tax, 2),
                'computed_total'  => number_format($compTot, 2),
                'payment_mode'    => e($r->payment_mode ?: '-'),
                'status'          => e($r->status),
                'action'          => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
    }

    public function expensesPage()
    {
        return view('reports.expenses', [
            'branches' => DB::table('branches')
                ->select('id', 'name')
                ->where('is_deleted', 'no')
                ->orderBy('name')->get(),
            // If you have an expense_categories table, this will populate the dropdown.
            'categories' => DB::table('expense_categories')
                ->select('id', 'name')
                ->orderBy('name')->get(),
            // If you use users table for created_by / user_id
            'users' => DB::table('users')->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function getExpensesData(Request $request)
    {
        $branchId   = $request->integer('branch_id');
        $catId      = $request->integer('expense_category_id');
        $userId     = $request->integer('user_id');
        $minAmount  = $request->filled('min_amount') ? (float)$request->input('min_amount') : null;
        $maxAmount  = $request->filled('max_amount') ? (float)$request->input('max_amount') : null;
        $search     = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        // Base with filters (no select/order/pagination yet)
        $base = DB::table('expenses as e')
            ->leftJoin('branches as b', 'b.id', '=', 'e.branch_id')
            ->leftJoin('expense_categories as ec', 'ec.id', '=', 'e.expense_category_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
            ->whereBetween('e.expense_date', [$start->toDateString(), $end->toDateString()])
            ->when($branchId, fn($q, $v) => $q->where('e.branch_id', $v))
            ->when($catId,    fn($q, $v) => $q->where('e.expense_category_id', $v))
            ->when($userId,   fn($q, $v) => $q->where('e.user_id', $v))
            ->when(!is_null($minAmount), fn($q) => $q->where('e.amount', '>=', request('min_amount')))
            ->when(!is_null($maxAmount), fn($q) => $q->where('e.amount', '<=', request('max_amount')));

        $totalRecords = (clone $base)->count();

        // Apply search to the base BEFORE computing filtered count & totals
        if (!empty($search)) {
            $sv = "%{$search}%";
            $base->where(function ($q) use ($sv) {
                $q->where('e.title', 'like', $sv)
                    ->orWhere('e.description', 'like', $sv)
                    ->orWhere('ec.name', 'like', $sv)
                    ->orWhere('b.name', 'like', $sv)
                    ->orWhere('u.name', 'like', $sv);
            });
        }

        $filteredRecords = (clone $base)->count();

        // ✅ Compute totals on a clean, searched & filtered clone (no select/order/limit)
        $totalsAmount = (clone $base)->sum('e.amount');

        // Now apply ordering & pagination for the row fetch
        $columns = [
            'e.id',           // 0 (ignored)
            'e.expense_date', // 1
            'b.name',         // 2
            'ec.name',        // 3
            'e.title',        // 4
            'e.description',  // 5
            'e.amount',       // 6
            'u.name',         // 7
            'e.created_at',   // 8
            null,             // 9
        ];

        if ($request->order) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $col = $columns[$idx] ?? 'e.expense_date';
            if ($col) $base->orderBy($col, $dir);
        } else {
            $base->orderBy('e.expense_date', 'desc')->orderBy('e.id', 'desc');
        }

        if ($request->length > 0) {
            $base->skip((int)$request->start)->take((int)$request->length);
        }

        // Row select happens only here (AFTER totals are computed)
        $rows = $base->selectRaw("
            e.id,
            e.expense_date,
            e.title,
            e.amount,
            e.description,
            e.user_id,
            e.branch_id,
            COALESCE(b.name, CONCAT('Branch #', e.branch_id)) as branch_name,
            e.expense_category_id,
            COALESCE(ec.name, CONCAT('Category #', e.expense_category_id)) as category_name,
            u.name as created_by,
            e.created_at
        ")
            ->get();

        $data = [];
        $startIdx = (int)$request->start;
        foreach ($rows as $i => $r) {
            $data[] = [
                'sr_no'        => $startIdx + $i + 1,
                'expense_date' => (string)$r->expense_date,
                'branch_name'  => e($r->branch_name ?? ''),
                'category'     => e($r->category_name ?? ''),
                'title'        => e($r->title ?? ''),
                'description'  => e($r->description ?? ''),
                'amount'       => number_format((float)$r->amount, 2),
                'created_by'   => e($r->created_by ?? ''),
                'created_at'   => $r->created_at ? Carbon::parse($r->created_at, $tz)->toDateTimeString() : '',
                'action'       => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
            'totals'          => [
                'amount' => round((float)$totalsAmount, 2),
            ],
        ]);
    }

    public function vendorPurchasesPage()
    {
        return view('reports.vendor_purchases', [
            'vendors' => DB::table('vendor_lists')
                ->select('id', 'name')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function getVendorPurchasesData(Request $request)
    {
        $vendorId   = $request->integer('vendor_id');
        $status     = $request->string('status')->toString(); // pending/approved/etc. or ''
        $minTotal   = $request->filled('min_total') ? (float)$request->input('min_total') : null;
        $maxTotal   = $request->filled('max_total') ? (float)$request->input('max_total') : null;
        $search     = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        // Subquery to aggregate purchase_products (ONLY_FULL_GROUP_BY safe)
        $ppAgg = DB::table('purchase_products')
            ->select(
                'purchase_id',
                DB::raw('SUM(qnt)    AS items_qty'),
                DB::raw('SUM(amount) AS items_amount')
            )
            ->groupBy('purchase_id');

        // Base builder with filters (no selects/order/limit yet)
        $base = DB::table('purchases as p')
            ->leftJoin('vendor_lists as v', 'v.id', '=', 'p.vendor_id')
            ->leftJoinSub($ppAgg, 'pp', function ($join) {
                $join->on('pp.purchase_id', '=', 'p.id');
            })
            ->whereBetween('p.date', [$start->toDateString(), $end->toDateString()])
            ->when($vendorId, fn($q, $v) => $q->where('p.vendor_id', $v))
            ->when($status !== '', fn($q) => $q->where('p.status', $status))
            ->when(!is_null($minTotal), fn($q) => $q->whereRaw('COALESCE(p.total_amount,p.total) >= ?', [request('min_total')]))
            ->when(!is_null($maxTotal), fn($q) => $q->whereRaw('COALESCE(p.total_amount,p.total) <= ?', [request('max_total')]));

        $totalRecords = (clone $base)->count();

        // Search
        if (!empty($search)) {
            $sv = "%{$search}%";
            $base->where(function ($q) use ($sv) {
                $q->where('p.bill_no', 'like', $sv)
                    ->orWhere('p.parchase_ledger', 'like', $sv) // column name as given
                    ->orWhere('p.status', 'like', $sv)
                    ->orWhere('v.name', 'like', $sv);
            });
        }

        $filteredRecords = (clone $base)->count();

        // Totals (on a clean, searched+filtered clone: no select/order/limit)
        $totals = (clone $base)->selectRaw('
                COALESCE(SUM(COALESCE(pp.items_qty,0)),0)    AS qty_total,
                COALESCE(SUM(COALESCE(pp.items_amount,0)),0) AS items_total,
                COALESCE(SUM(COALESCE(p.total_amount,p.total)),0) AS grand_total
            ')->first();

        // Ordering (must map to Blade columns)
        $columns = [
            'p.id',                                  // 0 Sr No (ignored)
            'p.date',                                // 1 Date
            'p.bill_no',                             // 2 Bill No
            'v.name',                                // 3 Vendor
            'p.parchase_ledger',                     // 4 Ledger
            DB::raw('COALESCE(pp.items_qty,0)'),     // 5 Items Qty
            DB::raw('COALESCE(pp.items_amount,0)'),  // 6 Items Amount
            'p.excise_fee',                          // 7 Excise Fee
            'p.vat',                                 // 8 VAT
            'p.tcs',                                 // 9 TCS
            DB::raw('COALESCE(p.total_amount,p.total)'), // 10 Grand Total
            'p.status',                              // 11 Status
        ];

        if ($request->order) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $col = $columns[$idx] ?? 'p.date';
            $base->orderBy($col, $dir);
        } else {
            $base->orderBy('p.date', 'desc')->orderBy('p.id', 'desc');
        }

        // Pagination
        if ($request->length > 0) {
            $base->skip((int)$request->start)->take((int)$request->length);
        }

        // Select for rows (now it’s safe to add columns)
        $rows = $base->selectRaw('
                p.id, p.bill_no, p.vendor_id, p.parchase_ledger, p.date,
                COALESCE(v.name, CONCAT("Vendor #", p.vendor_id)) AS vendor_name,
                COALESCE(pp.items_qty,0)    AS items_qty,
                COALESCE(pp.items_amount,0) AS items_amount,
                p.excise_fee, p.composition_vat, p.surcharge_on_ca, p.tcs,
                p.aed_to_be_paid, p.vat, p.surcharge_on_vat, p.blf, p.permit_fee,
                p.rsgsm_purchase, p.case_purchase, p.case_purchase_per, p.case_purchase_amt,
                COALESCE(p.total_amount,p.total) AS grand_total,
                p.status, p.created_at
            ')
            ->get();

        // Build payload
        $data = [];
        $startIdx = (int)$request->start;
        foreach ($rows as $i => $r) {
            // Optional: compute combined "other charges" for compact display
            $charges_total = (float)($r->excise_fee ?? 0) + (float)($r->composition_vat ?? 0) +
                (float)($r->surcharge_on_ca ?? 0) + (float)($r->tcs ?? 0) +
                (float)($r->aed_to_be_paid ?? 0) + (float)($r->vat ?? 0) +
                (float)($r->surcharge_on_vat ?? 0) + (float)($r->blf ?? 0) +
                (float)($r->permit_fee ?? 0) + (float)($r->rsgsm_purchase ?? 0) +
                (float)($r->case_purchase ?? 0) + (float)($r->case_purchase_amt ?? 0);

            $billLink = '<span class="badge bg-primary">' . e($r->bill_no) . '</span>'; // replace with a route if you have a view page

            $data[] = [
                'sr_no'         => $startIdx + $i + 1,
                'date'          => (string)$r->date,
                'bill_no'       => $billLink,
                'vendor_name'   => e($r->vendor_name),
                'ledger'        => e($r->parchase_ledger ?? ''),
                'items_qty'     => number_format((float)$r->items_qty, 0),
                'items_amount'  => number_format((float)$r->items_amount, 2),
                'excise_fee'    => number_format((float)$r->excise_fee, 2),
                'vat'           => number_format((float)$r->vat, 2),
                'tcs'           => number_format((float)$r->tcs, 2),
                'other_charges' => number_format($charges_total, 2),
                'grand_total'   => number_format((float)$r->grand_total, 2),
                'status'        => e($r->status),
                'action'        => '',
            ];
        }

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
            'totals'          => [
                'qty'         => (float)($totals->qty_total ?? 0),
                'items_total' => round((float)($totals->items_total ?? 0), 2),
                'grand_total' => round((float)($totals->grand_total ?? 0), 2),
            ],
        ]);
    }

    public function customerOutstandingPage()
    {
        return view('reports.customer_outstanding', [
            'branches' => DB::table('branches')->select('id', 'name')
                ->where('is_deleted', 'no')->orderBy('name')->get(),
            'parties'  => DB::table('party_users')->select('id', 'first_name', 'last_name', 'mobile_number')
                ->where('is_delete', 'No')->orderBy('first_name')->get(),
        ]);
    }

    public function getCustomerOutstandingData(Request $request)
    {
        $branchId    = $request->integer('branch_id');     // credit_histories.store_id
        $partyId     = $request->integer('party_user_id'); // optional
        $minOuts     = $request->filled('min_outstanding') ? (float)$request->input('min_outstanding') : null;
        $searchValue = $request->input('search.value');

        $tz    = config('app.timezone', 'Asia/Kolkata');
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $end   = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfMonth();

        // Preload branch & party maps
        $branchMap = DB::table('branches')->pluck('name', 'id')->all();
        $partyRows = DB::table('party_users')
            ->select('id', 'first_name', 'last_name', 'mobile_number', 'email', 'status')
            ->where('is_delete', 'No')
            ->when($partyId, fn($q, $v) => $q->where('id', $v))
            ->get();

        $partyMap = [];
        foreach ($partyRows as $p) {
            $partyMap[$p->id] = [
                'name'   => trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) ?: ('Party #' . $p->id),
                'mobile' => $p->mobile_number,
                'email'  => $p->email,
                'status' => $p->status,
            ];
        }

        // Pull all ledger transactions up to END (for FIFO/outstanding) + we’ll also mark which are < START (opening) or within period
        $txAll = DB::table('credit_histories as ch')
            ->select('ch.party_user_id', 'ch.store_id', 'ch.type', 'ch.credit_amount', 'ch.debit_amount', 'ch.created_at', 'ch.transaction_kind', 'ch.status')
            ->when($branchId, fn($q, $v) => $q->where('ch.store_id', $v))
            ->when($partyId,  fn($q, $v) => $q->where('ch.party_user_id', $v))
            ->where('ch.created_at', '<=', $end)
            ->orderBy('ch.party_user_id')->orderBy('ch.store_id')->orderBy('ch.created_at')
            ->get();

        // Accumulators per (party|store)
        // key format: "{$partyId}|{$storeId}"
        $rowsByKey = []; // holds sums + FIFO credits + info

        $makeKey = function ($pid, $sid) {
            return $pid . '|' . (int)$sid;
        };

        foreach ($txAll as $t) {
            $pid = (int)$t->party_user_id;
            if (!$pid) continue;

            $sid = (int)($t->store_id ?? 0);
            $key = $makeKey($pid, $sid);

            if (!isset($rowsByKey[$key])) {
                $rowsByKey[$key] = [
                    'party_id'     => $pid,
                    'store_id'     => $sid,
                    'opening_cr'   => 0.0,
                    'opening_dr'   => 0.0,
                    'period_cr'    => 0.0,
                    'period_dr'    => 0.0,
                    'last_tx_date' => null,

                    // for FIFO aging as of END
                    'fifo_credits' => [], // each: ['amount'=>float,'date'=>Y-m-d]
                    'total_cr_upto_end' => 0.0,
                    'total_dr_upto_end' => 0.0,
                ];
            }

            $date = Carbon::parse($t->created_at, $tz);

            $isCredit = ($t->type === 'credit');
            $crAmt    = $isCredit ? (float)$t->credit_amount : 0.0;
            $drAmt    = !$isCredit ? (float)$t->debit_amount  : 0.0;

            // Opening vs Period buckets (for columns)
            if ($date->lt($start)) {
                if ($isCredit) $rowsByKey[$key]['opening_cr'] += $crAmt;
                else           $rowsByKey[$key]['opening_dr'] += $drAmt;
            } else { // between start..end (inclusive)
                if ($isCredit) $rowsByKey[$key]['period_cr'] += $crAmt;
                else           $rowsByKey[$key]['period_dr'] += $drAmt;
            }

            // Track last activity date
            $rowsByKey[$key]['last_tx_date'] = $date->toDateString();

            // For Outstanding as of END: maintain FIFO of credits & apply debits
            if ($isCredit && $crAmt > 0) {
                $rowsByKey[$key]['fifo_credits'][] = ['amount' => $crAmt, 'date' => $date->toDateString()];
                $rowsByKey[$key]['total_cr_upto_end'] += $crAmt;
            } elseif ($drAmt > 0) {
                $rowsByKey[$key]['total_dr_upto_end'] += $drAmt;
                $remain = $drAmt;

                // Apply to oldest credits first
                for ($i = 0; $i < count($rowsByKey[$key]['fifo_credits']) && $remain > 0; $i++) {
                    $cAmt = $rowsByKey[$key]['fifo_credits'][$i]['amount'];
                    if ($cAmt <= 0) continue;
                    $use = min($cAmt, $remain);
                    $rowsByKey[$key]['fifo_credits'][$i]['amount'] = $cAmt - $use;
                    $remain -= $use;
                }
                // If remain > 0, that means overpayment; we’ll allow negative outstanding later.
            }
        }

        // Build result rows with aging buckets
        $list = [];
        foreach ($rowsByKey as $key => $r) {
            [$pid, $sid] = array_map('intval', explode('|', $key));
            $party = $partyMap[$pid] ?? ['name' => "Party #$pid", 'mobile' => null, 'email' => null, 'status' => null];

            $opening = $r['opening_cr'] - $r['opening_dr'];
            $periodC = $r['period_cr'];
            $periodD = $r['period_dr'];

            // FIFO outstanding as of END
            $outstanding = 0.0;
            $age0_30 = $age31_60 = $age61_90 = $age90p = 0.0;

            $endDate = $end->copy()->startOfDay();
            $remainingCredits = $r['fifo_credits'];

            // Sum leftover credit amounts and bucket them by age (days from credit date to END)
            foreach ($remainingCredits as $c) {
                $amt = (float)$c['amount'];
                if ($amt <= 0) continue;

                $outstanding += $amt;

                $cDate = Carbon::parse($c['date'], $tz)->startOfDay();
                $days  = $cDate->diffInDays($endDate); // 0 if same day

                if ($days <= 30)        $age0_30  += $amt;
                elseif ($days <= 60)    $age31_60 += $amt;
                elseif ($days <= 90)    $age61_90 += $amt;
                else                    $age90p   += $amt;
            }

            // Sanity: outstanding should also equal total_cr_upto_end - total_dr_upto_end (can be negative)
            $sanity = $r['total_cr_upto_end'] - $r['total_dr_upto_end'];
            // Use FIFO outstanding (non-negative per bucket), but we expose closing_outstanding = sanity
            $closingOutstanding = round($sanity, 2);

            // Min outstanding filter (on absolute or positive?) Usually we filter positive > X
            if (!is_null($minOuts) && $closingOutstanding < $minOuts) continue;

            $list[] = [
                'party_id'     => $pid,
                'store_id'     => $sid,
                'party_name'   => $party['name'],
                'mobile'       => $party['mobile'],
                'branch_name'  => $branchMap[$sid] ?? ('Branch #' . $sid),
                'opening'      => round($opening, 2),
                'period_credit' => round($periodC, 2),
                'period_debit' => round($periodD, 2),
                'closing'      => $closingOutstanding,
                'age_0_30'     => round($age0_30, 2),
                'age_31_60'    => round($age31_60, 2),
                'age_61_90'    => round($age61_90, 2),
                'age_90_plus'  => round($age90p, 2),
                'last_tx_date' => $r['last_tx_date'],
            ];
        }

        // Search over party/branch/mobile
        if (!empty($searchValue)) {
            $sv = mb_strtolower($searchValue);
            $list = array_values(array_filter($list, function ($row) use ($sv) {
                return str_contains(mb_strtolower($row['party_name']), $sv)
                    || str_contains(mb_strtolower($row['branch_name']), $sv)
                    || str_contains(mb_strtolower((string)$row['mobile']), $sv);
            }));
        }

        // Totals (for current filtered set)
        $totalRecords = count($list);
        $recordsFiltered = $totalRecords;

        // Ordering
        $columns = [
            null,            // 0 Sr No
            'party_name',    // 1
            'branch_name',   // 2
            'mobile',        // 3
            'opening',       // 4
            'period_credit', // 5
            'period_debit',  // 6
            'closing',       // 7
            'age_0_30',      // 8
            'age_31_60',     // 9
            'age_61_90',     // 10
            'age_90_plus',   // 11
            'last_tx_date',  // 12
            null             // 13 Actions
        ];
        if ($request->order) {
            $idx = (int)$request->order[0]['column'];
            $dir = strtolower($request->order[0]['dir'] ?? 'desc');
            $key = $columns[$idx] ?? 'closing';
            if ($key) {
                usort($list, function ($a, $b) use ($key, $dir) {
                    $av = $a[$key];
                    $bv = $b[$key];
                    if ($av == $bv) return 0;
                    if ($dir === 'asc') return ($av <=> $bv);
                    return ($bv <=> $av);
                });
            }
        } else {
            // default: highest outstanding first
            usort($list, fn($a, $b) => ($b['closing'] <=> $a['closing']));
        }

        // Pagination
        $startIdx = (int)$request->start;
        $length   = (int)$request->length;
        $paged    = $length > 0 ? array_slice($list, $startIdx, $length) : $list;

        // Build DataTables payload
        $data = [];
        foreach ($paged as $i => $r) {
            $data[] = [
                'sr_no'         => $startIdx + $i + 1,
                'party_name'    => e($r['party_name']),
                'branch_name'   => e($r['branch_name']),
                'mobile'        => e($r['mobile'] ?? ''),
                'opening'       => number_format($r['opening'], 2),
                'period_credit' => number_format($r['period_credit'], 2),
                'period_debit'  => number_format($r['period_debit'], 2),
                'closing'       => number_format($r['closing'], 2),
                'age_0_30'      => number_format($r['age_0_30'], 2),
                'age_31_60'     => number_format($r['age_31_60'], 2),
                'age_61_90'     => number_format($r['age_61_90'], 2),
                'age_90_plus'   => number_format($r['age_90_plus'], 2),
                'last_tx_date'  => e($r['last_tx_date'] ?? ''),
                'action'        => '', // add "View Ledger" button if you have a route
            ];
        }

        // Summary totals for badges
        $sumClosing = array_sum(array_column($list, 'closing'));
        $sum0_30    = array_sum(array_column($list, 'age_0_30'));
        $sum31_60   = array_sum(array_column($list, 'age_31_60'));
        $sum61_90   = array_sum(array_column($list, 'age_61_90'));
        $sum90p     = array_sum(array_column($list, 'age_90_plus'));

        return response()->json([
            'draw'            => (int)$request->draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
            'totals'          => [
                'closing'   => round($sumClosing, 2),
                'age_0_30'  => round($sum0_30, 2),
                'age_31_60' => round($sum31_60, 2),
                'age_61_90' => round($sum61_90, 2),
                'age_90p'   => round($sum90p, 2),
            ],
        ]);
    }
}
