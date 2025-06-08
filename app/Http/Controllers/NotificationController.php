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

            // dd($expiredProducts);
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

        if ($type === 'approved_stock') {
            $data = json_decode($nf->details);
            $id  = $data->id;
            $stockRequest = StockRequest::with(['branch', 'tobranch', 'user', 'items.product'])->findOrFail($id);
            return view('notification.stock-approved-form', compact('stockRequest'));
        }

        if ($type === 'transfer_stock') {
            $data = json_decode($nf->details);
            $id  = $data->id;
            $from_store = $data->from_store;
            $to_store = $data->to_store;
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

            return view('notification.stock-transfer-form', compact('stockTransfer', 'from_store', 'to_store'));
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

        $data['data'] = $res_date;
        $data['res_all_unread'] = $res_all_unread;
        $data['res_all'] = $res_all;
        return response()->json($data, 201);
    }
}
