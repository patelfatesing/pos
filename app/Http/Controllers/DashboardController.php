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

        // $branchId = $request->integer('branch_id');

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
            'data_pur' => $data_pur
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
