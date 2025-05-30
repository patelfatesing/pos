<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\StockRequest;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // app/Http/Controllers/PopupController.php

    public function loadForm($type, Request $request)
    {
        $nf_id = $request->nfid;

        $nf = Notification::find($nf_id);
        $nf->status = 'read';
        $nf->save();

        if ($type === 'low_stock') {
            $id  = $request->id;
            $lowStockProducts = DB::table('products')
                ->select(
                    'products.id',
                    'products.name',
                    'products.brand',
                    'products.sku',
                    'products.reorder_level',
                    DB::raw('IFNULL(SUM(inventories.quantity), 0) as total_stock')
                )
                ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
                ->where('products.is_deleted', 'no')
                ->where('products.is_active', 'yes')
                ->where('products.id', $id)
                ->groupBy(
                    'products.id',
                    'products.name',
                    'products.brand',
                    'products.sku',
                    'products.reorder_level'
                )
                ->havingRaw('total_stock <= products.reorder_level')
                ->get();
            return view('notification.product-form', compact('lowStockProducts'));
            // return view('notification.product-form'); // resources/views/popups/user-form.blade.php
        }

        if ($type === 'expire_product') {
            $expiredProducts = DB::table('inventories as i')
                ->join('products as p', 'i.product_id', '=', 'p.id')
                ->where('i.expiry_date', '<', Carbon::today())  // Get expired products
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
                ->orderBy('i.expiry_date', 'asc')  // Order by expiry date
                ->orderBy('i.batch_no')           // Order by batch number
                ->get();
            // dd($expiredProducts);
            return view('notification.expire-product-form', compact('expiredProducts'));
        }

        if ($type === 'request_stock') {
            $id  = $request->id;
            $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
            return view('notification.stock-request-form', compact('stockRequest'));
        }

        if ($type === 'approved_stock') {
            $id  = $request->id;
            $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
            return view('notification.stock-approved-form', compact('stockRequest'));
        }


        if ($type === 'transfer_stock') {
            $id  = $request->id;
            $stockTransfer =  DB::table('stock_transfers as i')
                ->join('products as p', 'i.product_id', '=', 'p.id')
                ->where('i.transfer_number', $id)
                ->select(
                    'i.id as id',
                    'i.product_id',
                    'p.name as product_name',
                    'p.brand',
                    'i.transfer_number',
                    'i.quantity',
                    'p.sku',
                    'p.barcode',
                    'i.to_branch_id'
                )
                ->orderBy('i.created_at')
                ->get();

            return view('notification.stock-transfer-form', compact('stockTransfer'));
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

        // **Search filter**
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('notifications.type', 'like', '%' . $searchValue . '%')
                    ->orWhere('notifications.content', 'like', '%' . $searchValue . '%')
                    ->orWhere('notifications.notify_to', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.name', 'like', '%' . $searchValue . '%'); // add search on user name
            });
        }

        $recordsTotal = Notification::whereNull('notify_to')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        foreach ($data as $notification) {
            $records[] = [
                'type' => $notification->type,
                'content' => $notification->content,
                'created_by' => $notification->created_by_name ?? 'System',
                'status' => $notification->status,
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
