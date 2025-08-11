<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function loadForm($type, Request $request)
    {
        $nf_id = $request->nfid;
  
        $nf = Notification::find($nf_id);     
        $nf->status = 'read';
        $nf->save();

        if ($type === 'low_stock') {

            $data = json_decode($nf->details);
            $ids = explode(',', $data->product_id);
            $branch_name = '';

            if (!empty($data->store_id)) {
                $branch = Branch::where('id', $data->store_id)->first();
                $branch_name = $branch->name;
            }

            $lowStockProducts = DB::table('products')
                ->select(
                    'products.id',
                    'products.name',
                    'products.brand',
                    'products.sku',
                    'inventories.low_level_qty',
                    DB::raw('IFNULL(SUM(inventories.quantity), 0) as total_stock')
                )
                ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
                ->where('products.is_deleted', 'no')
                ->where('products.is_active', 'yes')
                ->where('inventories.store_id', $data->store_id)
                ->whereIn('products.id', $ids) // <- Use array here
                ->groupBy(
                    'products.id',
                    'products.name',
                    'products.brand',
                    'products.sku',
                    'inventories.low_level_qty'
                )
                // ->havingRaw('total_stock <= products.reorder_level')
                ->get();

            return view('notification.product-form', compact('lowStockProducts', 'branch_name'));
        }

        if ($type === 'expire_product') {


            $query = DB::table('inventories as i')
                ->join('products as p', 'i.product_id', '=', 'p.id')
                ->whereBetween('i.expiry_date', [
                    Carbon::today(),
                    Carbon::today()->addDays(5)
                ]);

            if (Auth::user()->role['name'] !== "admin") {

                $data = User::with('userInfo')
                    ->where('users.id', Auth::id())
                    ->where('is_deleted', 'no')
                    ->firstOrFail();
                $branch_id = $data->userInfo->branch_id;
                $query->where('i.store_id', $branch_id);
            }

            $expiredProducts = $query
                ->where('i.quantity', '>', 0)
                ->select(
                    'i.id as inventory_id',
                    'i.product_id',
                    'p.name as product_name',
                    'p.brand',
                    'i.batch_no',
                    'i.expiry_date',
                    'i.quantity',
                    'p.sku',
                    'p.barcode',
                    'i.store_id',
                    'i.location_id'
                )
                ->orderBy('i.expiry_date', 'asc')
                ->orderBy('i.batch_no')
                ->get();

            return view('notification.expire-product-form', compact('expiredProducts'));
        }

        if ($type === 'request_stock') {

            $data = json_decode($nf->details);
            $id  = $data->id;

            $branch_name = '';

            if (!empty($data->store_id)) {
                $branch = Branch::where('id', $data->store_id)->first();
                $branch_name = $branch->name;
            }
            $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
            return view('notification.stock-request-form', compact('stockRequest', 'branch_name'));
        }

        if ($type === 'rejected_stock') {

            $data = json_decode($nf->details);
            $id  = $data->id;

            $branch_name = '';

            if (!empty($data->store_id)) {
                $branch = Branch::where('id', $data->store_id)->first();
                $branch_name = $branch->name;
            }
            $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
            return view('notification.stock-reject-form', compact('stockRequest', 'branch_name'));
        }


        if ($type === 'approved_stock') {
            $data = json_decode($nf->details);
            $id  = $data->id;
            if ($data->type == 'approved_stock') {
                $transfer_type = $data->type;
                $stockTransfer = DB::table('stock_request_approves as sra')
                    ->join('stock_request_items as sri', 'sra.stock_request_item_id', '=', 'sri.id')
                    ->join('stock_requests as sr', 'sra.stock_request_id', '=', 'sr.id')
                    ->join('products as p', 'sra.product_id', '=', 'p.id')
                    ->join('branches as sb', 'sra.source_store_id', '=', 'sb.id') // Join to get source branch name
                    ->where('sra.stock_request_id', $data->req_id)
                    ->where('sra.destination_store_id', $data->store_id)
                    ->select(
                        'sra.id as id',
                        'sra.product_id',
                        'p.name as product_name',
                        'p.brand',
                        'sra.stock_request_id',
                        'sra.approved_quantity',
                        'sra.destination_store_id',
                        'sra.source_store_id',
                        'sb.name as source_branch_name', // Add source branch name
                        'sra.approved_at',
                        'sr.status',
                        'p.sku',
                        'p.barcode'
                    )
                    ->orderBy('sra.approved_at', 'desc')
                    ->get();


                $stockRequest = StockRequest::with(['branch', 'tobranch', 'user', 'items.product'])->findOrFail($id);

                return view('notification.stock-approved-form', compact('stockRequest', 'stockTransfer', 'transfer_type'));
            } else {
                $stockRequest = StockRequest::with(['branch', 'tobranch', 'user', 'items.product'])->findOrFail($id);
                return view('notification.stock-approved-form', compact('stockRequest'));
            }
        }

        if ($type === 'transfer_stock') {
            $data = json_decode($nf->details);
            $id  = $data->id;
            $from_store = $data->from_store;
            $to_store = $data->to_store;

            $transfer_type = $data->type;
            if ($data->type == 'approved_stock') {


                $stockTransfer =  DB::table('stock_request_approves as sra')
                    ->join('stock_request_items as sri', 'sra.stock_request_item_id', '=', 'sri.id')
                    ->join('stock_requests as sr', 'sra.stock_request_id', '=', 'sr.id')
                    ->join('products as p', 'sra.product_id', '=', 'p.id')
                    ->where('sra.stock_request_id', $data->req_id)
                    ->where('sra.source_store_id', $data->store_id)
                    ->select(
                        'sra.id as id',
                        'sra.product_id',
                        'p.name as product_name',
                        'p.brand',
                        'sra.stock_request_id',
                        'sra.approved_quantity',
                        'sra.destination_store_id',
                        'sra.source_store_id',
                        'sra.approved_at',
                        'sr.status',
                        'p.sku',
                        'p.barcode'
                    )
                    ->orderBy('sra.approved_at', 'desc')
                    ->get();
            } else {

                $stockTransfer =  DB::table('stock_transfers as i')
                    ->join('products as p', 'i.product_id', '=', 'p.id')
                    ->where('i.transfer_number', $id)
                    ->select(
                        'i.id as id',
                        'i.product_id',
                        'p.name as product_name',
                        'p.brand',
                        'i.transfer_number',
                        'i.transferred_at',
                        'i.status',
                        'i.quantity',
                        'p.sku',
                        'p.barcode',
                        'i.to_branch_id'
                    )
                    ->orderBy('i.created_at')
                    ->get();
            }

            return view('notification.stock-transfer-form', compact('stockTransfer', 'from_store', 'to_store', 'transfer_type'));
        }



        if ($type === 'price_change') {
            $data = json_decode($nf->details);
            $id  = $data->id;
            // $from_store = $data->from_store;
            // $to_store = $data->to_store;
            $priceChange =  DB::table('product_price_change_history as ppl')
                ->join('products as p', 'ppl.product_id', '=', 'p.id')
                ->orderBy('ppl.changed_at', 'desc')
                ->select('p.name', 'ppl.old_price', 'ppl.new_price', 'ppl.changed_at', 'ppl.created_at')
                ->where('ppl.id', $id)
                ->take(10)
                ->first();

            return view('notification.price-change-form', compact('priceChange'));
        }

        return response()->json(['error' => 'Form not found'], 404);
    }

    public function index()
    {
        $notifications = Notification::latest()->get();
        return view('notification.list', compact('notifications'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input('columns.' . $orderColumnIndex . '.data', 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $query = Notification::query()
            ->leftJoin('users', 'notifications.created_by', '=', 'users.id')
            ->select(
                'notifications.id',
                'notifications.type',
                'notifications.content',
                'notifications.status',
                'notifications.created_at',
                'users.name as created_by_name'
            )
            ->whereNull('notifications.notify_to');

        // Type filter
        if ($request->filled('type')) {
            $query->where('notifications.type', $request->type);
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('notifications.created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        // Search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('notifications.type', 'like', '%' . $searchValue . '%')
                    ->orWhere('notifications.content', 'like', '%' . $searchValue . '%')
                    ->orWhere('notifications.notify_to', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.name', 'like', '%' . $searchValue . '%');
            });
        }

        // Get total records count before applying filters
        $recordsTotal = Notification::whereNull('notify_to')->count();

        // Get filtered records count
        $recordsFiltered = $query->count();

        // Get paginated and ordered data
        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];
        foreach ($data as $notification) {
            $records[] = [
                'type' => ucwords(str_replace('_', ' ', $notification->type)), // Format type for display
                'content' => $notification->content,
                'created_by' => $notification->created_by_name ?? 'System',
                'status' => ucfirst($notification->status), // Capitalize status
                'created_at' => Carbon::parse($notification->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

    public function getNotication()
    {

        if (session('role_name') == "admin") {
            $res_date =  Notification::whereNull('notify_to')
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $res_all = Notification::where('notify_to', null)
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->orderBy('created_at', 'desc')
                // ->limit(10)
                ->count();

            $res_all_unread = Notification::where('notify_to', null)
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->where('status', 'unread')
                ->orderBy('created_at', 'desc')
                ->count();
        } else {

            $data = User::with('userInfo')
                ->where('users.id', Auth::id())
                ->where('is_deleted', 'no')
                ->firstOrFail();

            $branch_id = $data->userInfo->branch_id;

            $res_date = Notification::where('notify_to', $branch_id)
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $res_all_unread = Notification::where('notify_to', null)
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->where('status', 'unread')
                ->orderBy('created_at', 'desc')
                ->count();

            $res_all = Notification::where('notify_to', $branch_id)
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->orderBy('created_at', 'desc')
                ->count();
        }

        $url = url('notifications/expired-product/');

        $data['data'] = $res_date;
        $data['res_all_unread'] = $res_all_unread;
        $data['res_all'] = $res_all;
        $data['url'] = $url;
        return response()->json($data, 201);
    }

    public function viewExpiredProducts($id, Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search'); // <-- Search term

        // $branch = Branch::findOrFail($id);
        // $branch_name = $branch->name;

        // Build the query for expired products
        $query = DB::table('inventories as i')
            ->join('products as p', 'i.product_id', '=', 'p.id')
            ->whereBetween('i.expiry_date', [
                Carbon::today(),
                Carbon::today()->addDays(5)
            ]);

        // Apply branch restriction if the user is not an admin
        if (Auth::user()->role['name'] !== "admin") {
            $data = User::with('userInfo')
                ->where('users.id', Auth::id())
                ->where('is_deleted', 'no')
                ->firstOrFail();
            $branch_id = $data->userInfo->branch_id;
            $query->where('i.store_id', $branch_id);
        }

        // Apply search filter for product name or SKU
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.name', 'like', '%' . $search . '%')
                    ->orWhere('p.sku', 'like', '%' . $search . '%');
            });
        }

        // Get totals before pagination (sum of quantities)
        $totals = (clone $query)
            ->where('i.quantity', '>', 0)
            ->selectRaw('SUM(i.quantity) as total_quantity')
            ->first();

        // Apply pagination
        $expiredProducts = $query
            ->where('i.quantity', '>', 0)
            ->select(
                'i.id as inventory_id',
                'i.product_id',
                'p.name as product_name',
                'p.brand',
                'i.batch_no',
                'i.expiry_date',
                'i.quantity',
                'p.sku',
                'p.barcode',
                'i.store_id',
                'i.location_id'
            )
            ->orderBy('i.expiry_date', 'asc')
            ->orderBy('i.batch_no')
            ->paginate($perPage)
            ->appends(['per_page' => $perPage, 'search' => $search]); // Preserve search

        // Return the view with data
        return view('notification.view', compact(
            'expiredProducts',
            'id',
            'perPage',
            'totals'
        ) + [
            'totalQuantity' => $totals->total_quantity,
            'search' => $search,
        ]);
    }
}
