<?php

// app/Http/Controllers/SalesReportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use Carbon\Carbon;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use App\Events\DrawerOpened;
use App\Events\NewCreditTransaction;
use Illuminate\Support\Facades\Auth;
use App\Models\PartyUserImage;
use App\Models\CommissionUserImage;
use Illuminate\Support\Facades\Log;

class SalesReportController extends Controller
{
    public function index()
    {
        $branches = DB::table('branches')->get(); // Adjust if you use a model
        return view('sales.index', compact('branches'));
    }

    public function salasList()
    {
        $branches = DB::table('branches')->get(); // Adjust if you use a model
        return view('sales.sales-list', compact('branches'));
    }

    public function getData(Request $request)
    {
        $query = DB::table('invoices')
            ->join('branches', 'invoices.branch_id', '=', 'branches.id')
            ->select(
                'invoices.id',
                'invoices.invoice_number',
                'invoices.party_amount',
                'invoices.status',
                'invoices.sub_total',
                'invoices.tax',
                'invoices.commission_amount',
                'invoices.creditpay',
                'invoices.total',
                'invoices.items',
                'invoices.branch_id',
                'branches.name as branch_name',
                'invoices.created_at',
                'invoices.commission_user_id',
                'invoices.payment_mode',
                'invoices.party_user_id'
            );

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('invoices.created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        if (!empty($request->branch_id)) {
            $query->where('invoices.branch_id', $request->branch_id);
        }

        $totalRecords = $query->count();

        // Search
        if (!empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('invoices.invoice_number', 'like', "%{$searchValue}%")
                    ->orWhere('invoices.status', 'like', "%{$searchValue}%")
                    ->orWhere('branches.name', 'like', "%{$searchValue}%");
            });
        }

        $filteredRecords = $query->count();

        // Sorting
        if ($request->order) {
            $columns = ['invoices.id', 'invoices.invoice_number', 'invoices.status', 'invoices.sub_total', 'invoices.tax', 'invoices.total', 'invoices.commission_amount', 'invoices.branch_id', 'invoices.party_amount', 'invoices.items', 'invoices.created_at', 'branches.name'];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'invoices.created_at';
            $orderDir = $request->order[0]['dir'] ?? 'desc';
            $query->orderBy($orderColumn, $orderDir);
        }

        // Pagination
        $query->skip($request->start)
            ->take($request->length);

        $invoices = $query->get();

        $data = [];
        foreach ($invoices as $invoice) {
            $items = json_decode($invoice->items, true);
            $itemCount = collect($items)->sum('quantity');

            $action = '<div class="d-flex align-items-center list-action">
                        <a class="badge badge-success mr-2" data-toggle="tooltip" data-placement="top" title="View"
                        href="' . url('/view-invoice/' . $invoice->id) . '">' . $invoice->invoice_number . '</a>
                    </div> ';
            $photo = '<div class="d-flex align-items-center list-action">
                <a class="badge badge-success mr-2" onClick="showPhoto(' . ($invoice->id ?? '') . ',\'' . ($invoice->commission_user_id ?? '') . '\',\'' . ($invoice->party_user_id ?? '') . '\',\'' . ($invoice->invoice_number ?? '') . '\')">Show</a>
                </div>';

            $data[] = [
                'invoice_number' => $action,
                'status' => $invoice->status,
                'photo' => $photo,
                'sub_total' => number_format($invoice->sub_total, 2),
                'total' => number_format($invoice->total, 2),
                'commission_amount' => number_format($invoice->commission_amount, 2),
                'creditpay' => number_format($invoice->creditpay, 2),
                'party_amount' => number_format($invoice->party_amount, 2),
                'items_count' => $itemCount,
                'branch_name' => $invoice->branch_name,
                'payment_mode' => $invoice->payment_mode,
                'created_at' => Carbon::parse($invoice->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function show($id, Request $request)
    {
        $commission_user_id = $request->input('commission_user_id', null);
        $party_user_id = $request->input('party_user_id', null);
        $invoice_no = $request->input('invoice_no', null);


        // Debugging output
        // dd($id, $commission_user_id, $party_user_id);
        if (!empty($party_user_id)) {
            $photos = PartyUserImage::where('transaction_id', $id)->first();
        } else if (!empty($commission_user_id)) {
            $photos = CommissionUserImage::where('transaction_id', $id)->first();
        }
        $imageType = "";
        return view('party_users.cust-photo', compact('photos', 'invoice_no', 'imageType'));
    }

    public function storeSummary()
    {
        $invoices = DB::table('invoices')
            ->select('branch_id', 'items')
            ->where('status', '!=', 'cancelled') // or filter if needed
            ->get();

        $summary = [];

        foreach ($invoices as $invoice) {
            $items = json_decode($invoice->items, true);

            foreach ($items as $item) {
                $key = $invoice->branch_id . '|' . $item['name'];

                if (!isset($summary[$key])) {
                    $summary[$key] = [
                        'branch_id' => $invoice->branch_id,
                        'item_name' => $item['name'],
                        'total_quantity' => 0,
                        'total_amount' => 0,
                    ];
                }

                $quantity = (float) ($item['quantity'] ?? 1);
                $price = (float) ($item['price'] ?? 0);

                $summary[$key]['total_quantity'] += $quantity;
                $summary[$key]['total_amount'] += ($price * $quantity);
            }
        }

        // Re-index array
        $summary = array_values($summary);

        return response()->json($summary);
    }

    public function getSalesReportData(Request $request)
    {
        $query = DB::table('invoices')
            ->select(
                'invoices.id',
                'invoices.invoice_number',
                'invoices.sub_total',
                'invoices.tax',
                'invoices.total',
                'invoices.commission_amount', // <-- Added
                'invoices.creditpay',      // <-- Added
                'invoices.items',
                'invoices.status',
                'invoices.created_at',
                'branches.name as branch_name'
            )
            ->join('branches', 'invoices.branch_id', '=', 'branches.id');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('invoices.created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        if ($request->branch_id) {
            $query->where('invoices.branch_id', $request->branch_id);
        }

        $invoices = $query->orderBy('branches.name')
            ->orderBy('invoices.created_at', 'desc')
            ->get()
            ->groupBy('branch_name');

        $finalData = [];
        $totalItemsOverall = 0;
        $totalAmountOverall = 0;
        $totalCommissionOverall = 0;
        $totalPartyAmountOverall = 0;

        foreach ($invoices as $branchName => $branchInvoices) {
            $branchInvoices = $branchInvoices->take(3); // Only 3 invoices per branch

            $total_sub_total = 0.0;
            $total_tax = 0.0;
            $total_total = 0.0;
            $total_items_count = 0;
            $total_commission = 0.0;
            $total_party_amount = 0.0;

            foreach ($branchInvoices as $invoice) {
                $items = json_decode($invoice->items, true);
                $items_count = count($items);
                $invoice_sub_total = 0.0;

                foreach ($items as $item) {
                    $invoice_sub_total += (float)$item['quantity'] * (float)$item['mrp'];
                }

                // Add each invoice data to finalData
                $finalData[] = [
                    'branch_name'        => $branchName,
                    'invoice_number'     => $invoice->invoice_number,
                    'status'             => $invoice->status,
                    'sub_total'          => $invoice_sub_total,
                    'tax'                => (float)$invoice->tax,
                    'commission_amount'  => (float)$invoice->commission_amount,
                    'party_amount'       => (float)$invoice->creditpay,
                    'total'              => (float)$invoice->total,
                    'items_count'        => $items_count,
                    'created_at'         => Carbon::parse($invoice->created_at)->format('Y-m-d'),
                    'colspan'            => 9
                ];

                // Update totals
                $total_sub_total += $invoice_sub_total;
                $total_tax += (float)$invoice->tax;
                $total_commission += (float)$invoice->commission_amount;
                $total_party_amount += (float)$invoice->creditpay;
                $total_total += (float)$invoice->total;
                $total_items_count += $items_count;
            }

            // Add summary row for this branch
            $finalData[] = [
                'branch_name'        => $branchName . ' (Summary)',
                'invoice_number'     => '',
                'status'             => '',
                'sub_total'          => $total_sub_total,
                'tax'                => $total_tax,
                'commission_amount'  => $total_commission,
                'party_amount'       => $total_party_amount,
                'total'              => $total_total,
                'items_count'        => $total_items_count,
                'created_at'         => '',
                'colspan'            => 8
            ];

            // Track overall totals
            $totalItemsOverall += $total_items_count;
            $totalAmountOverall += $total_total;
            $totalCommissionOverall += $total_commission;
            $totalPartyAmountOverall += $total_party_amount;
        }

        // Optionally: Grand Total
        // $finalData[] = [
        //     'branch_name'        => 'Grand Total',
        //     'invoice_number'     => '',
        //     'status'             => '',
        //     'sub_total'          => '',
        //     'tax'                => '',
        //     'commission_amount'  => $totalCommissionOverall,
        //     'party_amount'       => $totalPartyAmountOverall,
        //     'total'              => $totalAmountOverall,
        //     'items_count'        => $totalItemsOverall,
        //     'created_at'         => '',
        //     'colspan'            => 5
        // ];

        return response()->json([
            'data' => $finalData
        ]);
    }

    public function salesDaily()
    {
        $branches = DB::table('branches')->get(); // Adjust if you use a model
        return view('sales.sales-daily', compact('branches'));
    }

    public function branchSalesReport(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir', 'desc');

        // Define the columns to sort
        $columns = [
            'date',
            'branch_name',
            'total_orders',
            'total_items',
            'total_sales'
        ];

        $orderColumn = $columns[$orderColumnIndex] ?? 'date';
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        $startDate = $request->start_date ?? now()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $branchId = $request->branch_id;

        // Base query
        $query = DB::table('invoices')
            ->select(
                DB::raw('DATE(invoices.created_at) as date'),
                'branches.name as branch_name',
                DB::raw('COUNT(invoices.id) as total_orders'),
                DB::raw('SUM(JSON_LENGTH(items)) as total_items'),
                DB::raw('SUM(total) as total_sales')
            )
            ->join('branches', 'invoices.branch_id', '=', 'branches.id')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('invoices.created_at', [$startDate, $endDate]);
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branches.id', $branchId);
            })
            ->groupBy(DB::raw('DATE(invoices.created_at)'), 'branches.id', 'branches.name');

        // Clone query for filtering count
        $filteredQuery = clone $query;

        // Apply search filter
        if (!empty($searchValue)) {
            $filteredQuery->having(function ($q) use ($searchValue) {
                $q->where('branch_name', 'like', "%$searchValue%")
                    ->orWhere('date', 'like', "%$searchValue%");
            });
        }

        $recordsFiltered = $filteredQuery->count();

        // Total records before filters (not paginated)
        $recordsTotal = DB::table('invoices')
            ->join('branches', 'invoices.branch_id', '=', 'branches.id')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('invoices.created_at', [$startDate, $endDate]);
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branches.id', $branchId);
            })
            ->select(DB::raw('DATE(invoices.created_at)'))
            ->groupBy(DB::raw('DATE(invoices.created_at)'), 'branches.id', 'branches.name')
            ->get()
            ->count();

        // Apply ordering, pagination, and get data
        $data = $filteredQuery
            ->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function stockReport()
    {
        $branches = DB::table('branches')
            ->where('is_deleted', 'no')
            ->get();

        $products = DB::table('products')
            ->where('is_deleted', 'no')
            ->where('is_active', 1)
            ->get();

        $categories = DB::table('categories')
            ->where('is_deleted', 'no')
            ->get();

        $subcategories = DB::table('sub_categories')
            ->where('is_deleted', 'no')
            ->get();

        return view('sales.stock_report', compact('branches', 'products', 'categories', 'subcategories'));
    }

    public function fetchStockData(Request $request)
    {
        try {
            Log::info('Fetch stock data request received', $request->all());

            $query = Inventory::select(
                'inventories.store_id',
                'products.id as product_id',
                'branches.name as branch_name',
                'products.name as product_name',
                'products.barcode',
                'categories.name as category_name',
                'products.mrp',
                'products.sell_price as selling_price',
                'products.discount_price as discount',
                'products.cost_price',
                DB::raw('COALESCE(SUM(daily_product_stocks.opening_stock), 0) as opening_stock'),
                DB::raw('COALESCE(SUM(daily_product_stocks.added_stock), 0) as in_qty'),
                DB::raw('COALESCE(SUM(daily_product_stocks.transferred_stock), 0) + COALESCE(SUM(daily_product_stocks.sold_stock), 0) as out_qty'),
                'inventories.quantity as all_qty',
                DB::raw('inventories.quantity * products.cost_price as all_price')
            )
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->join('branches', 'inventories.store_id', '=', 'branches.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('sub_categories', 'products.subcategory_id', '=', 'sub_categories.id')
                ->leftJoin('daily_product_stocks', function ($join) use ($request) {
                    $join->on('products.id', '=', 'daily_product_stocks.product_id')
                        ->on('branches.id', '=', 'daily_product_stocks.branch_id');

                    if ($request->start_date && $request->end_date) {
                        $join->whereBetween('daily_product_stocks.date', [
                            Carbon::parse($request->start_date)->startOfDay(),
                            Carbon::parse($request->end_date)->endOfDay()
                        ]);
                    }
                })
                ->where('products.is_active', 'yes')
                ->where('products.is_deleted', 'no')
                ->where('branches.is_deleted', 'no');

            // Apply filters
            if ($request->store_id) {
                $query->where('inventories.store_id', $request->store_id);
            }

            if ($request->product_id) {
                $query->where('products.id', $request->product_id);
            }

            if ($request->category_id) {
                $query->where('products.category_id', $request->category_id);
            }

            if ($request->subcategory_id) {
                $query->where('products.subcategory_id', $request->subcategory_id);
            }

            // Add grouping to prevent duplicates
            $query->groupBy(
                'inventories.store_id',
                'products.id',
                'branches.name',
                'products.name',
                'products.barcode',
                'categories.name',
                'products.mrp',
                'products.sell_price',
                'products.discount_price',
                'products.cost_price',
                'inventories.quantity'
            );

            // Debug the SQL query
            Log::info('SQL Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $stocks = $query->get();

            Log::info('Stock data retrieved', ['count' => $stocks->count()]);

            return response()->json([
                'draw' => $request->input('draw', 1),
                'recordsTotal' => $stocks->count(),
                'recordsFiltered' => $stocks->count(),
                'data' => $stocks
            ]);
        } catch (\Exception $e) {
            Log::error('Error in fetchStockData: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'error' => 'An error occurred while fetching stock data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function commissionReport()
    {

        // event(new DrawerOpened());
        // Optional: pass authenticated user
        // $user = Auth()->user();

        // Trigger the event
        // event(new DrawerOpened($user));
        // event(new DrawerOpened([
        //     'message' => 'New credit transaction added!',
        //     'customer' => 'John Doe', // You can pass real customer data here
        // ]));

        //   event(new NewCreditTransaction([
        //     'message' => 'New credit transaction added!',
        //     'customer' => 'John Doe', // You can pass real customer data here
        // ]));


        $party_users = DB::table('party_users')->get(); // Adjust if you use a model
        return view('reports.commission_list', compact('party_users'));
    }

    public function commissionInvoicesReport(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir', 'desc');

        $columns = [
            'invoice_id',
            'invoice_number',
            'invoice_date',
            'invoice_total',
            'commission_amount',
            'commission_user_id',
            'commission_user_name',
            'commission_type',
            'commission_value',
            'applies_to',
            'start_date',
            'end_date'
        ];

        $orderColumn = $columns[$orderColumnIndex] ?? 'invoice_date';
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        // Base query
        $query = DB::table('invoices as i')
            ->select(
                'i.id as invoice_id',
                'i.invoice_number',
                'i.created_at as invoice_date',
                'i.total as invoice_total',
                'i.creditpay as commission_amount',
                'cu.id as party_user_id',
                'cu.first_name as commission_user_name',
                'cu.credit_points',
                'ch.total_purchase_items',
                'ch.credit_amount',
                'ch.status',
                'ch.id as commission_id',

            )
            ->leftJoin('credit_histories as ch', 'i.id', '=', 'ch.invoice_id')
            ->leftJoin('party_users as cu', 'ch.party_user_id', '=', 'cu.id')
            ->whereNotNull('i.party_user_id');

        // Total record count before filters
        $recordsTotal = DB::table('invoices')
            ->whereNotNull('party_user_id')
            ->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('i.invoice_number', 'like', "%$searchValue%")
                    ->orWhere('cu.first_name', 'like', "%$searchValue%");
            });
        }

        if ($request->customer_id) {
            $query->where('cu.id', $request->customer_id);
        }

        // Count after filters
        $recordsFiltered = $query->count();

        // Get data with order and pagination
        $data = $query
            ->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
