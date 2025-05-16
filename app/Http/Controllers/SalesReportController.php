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
            ->select('id', 'invoice_number', 'status', 'sub_total', 'tax','commission_amount','party_amount', 'total', 'items', 'branch_id','created_at');
    
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }
    
        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }
    
        $totalRecords = $query->count(); // total records before filtering
    
        // Search
        if ($request->search['value']) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) {
                $q->where('invoice_number', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }
    
        $filteredRecords = $query->count(); // after search filter
    
        // Sorting
        if ($request->order) {
            $columns = ['id', 'invoice_number', 'status', 'sub_total', 'tax', 'total','commission_amount','branch_id','party_amount', 'items', 'created_at'];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'created_at';
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
                       </div>';
    
            $data[] = [
                'invoice_number' => $action,
                'status' => $invoice->status,
                'sub_total' => number_format($invoice->sub_total, 2),
                'total' => number_format($invoice->total, 2),
                'commission_amount' =>number_format($invoice->commission_amount, 2),
                'party_amount' =>number_format($invoice->party_amount, 2),
                'items_count' => $itemCount,
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
                'invoices.party_amount',      // <-- Added
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
                    $invoice_sub_total += (float)$item['quantity'] * (float)$item['price'];
                }

                // Add each invoice data to finalData
                $finalData[] = [
                    'branch_name'        => $branchName,
                    'invoice_number'     => $invoice->invoice_number,
                    'status'             => $invoice->status,
                    'sub_total'          => $invoice_sub_total,
                    'tax'                => (float)$invoice->tax,
                    'commission_amount'  => (float)$invoice->commission_amount,
                    'party_amount'       => (float)$invoice->party_amount,
                    'total'              => (float)$invoice->total,
                    'items_count'        => $items_count,
                    'created_at'         => Carbon::parse($invoice->created_at)->format('Y-m-d'),
                    'colspan'            => 9
                ];

                // Update totals
                $total_sub_total += $invoice_sub_total;
                $total_tax += (float)$invoice->tax;
                $total_commission += (float)$invoice->commission_amount;
                $total_party_amount += (float)$invoice->party_amount;
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
            'date', 'branch_name', 'total_orders', 'total_items', 'total_sales'
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
        $branches = DB::table('branches')->get(); // Adjust if you use a model
        return view('sales.stock_report', compact('branches'));
    }

    public function fetchStockData(Request $request)
    {
        $query = Inventory::select(
                'inventories.store_id',
                'products.id as product_id',
                \DB::raw('MAX(branches.name) as branch_name'),
                \DB::raw('MAX(products.name) as product_name'),
                \DB::raw('MAX(products.sku) as sku'),
                \DB::raw('MAX(products.reorder_level) as reorder_level'),
                \DB::raw('MAX(products.sell_price) as sell_price'),
                \DB::raw('SUM(inventories.quantity) as quantity')
            )
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('branches', 'inventories.store_id', '=', 'branches.id')
            ->where('products.is_active', 1);
            // ->where('products.is_deleted', 0)
            // ->where('branches.is_active', 1)
            // ->where('branches.is_deleted', 0);
    
        // Filter by branch if selected
        if ($request->branch_id) {
            $query->where('inventories.store_id', $request->branch_id);
        }
    
        $stocks = $query
            ->groupBy('inventories.store_id', 'products.id')
            ->get();
    
        return response()->json([
            'data' => $stocks
        ]);
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
            'invoice_id', 'invoice_number', 'invoice_date', 'invoice_total',
            'commission_amount', 'commission_user_id', 'commission_user_name',
            'commission_type', 'commission_value', 'applies_to', 'start_date', 'end_date'
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
                DB::raw("CONCAT(cu.first_name, ' ', cu.last_name) as commission_user_name"),
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
                ->orWhere(DB::raw("CONCAT(cu.first_name, ' ', cu.last_name)"), 'like', "%$searchValue%");
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
