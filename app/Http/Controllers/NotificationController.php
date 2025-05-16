<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\StockRequest;

class NotificationController extends Controller
{
    // app/Http/Controllers/PopupController.php

    public function loadForm($type,Request $request)
    {
        $nf_id = $request->nfid;

        $nf = Notification::find($nf_id);
        $nf->status = 'read';
        $nf->save();

        if ($type === 'low_stock') {
            
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
            ->groupBy(
                'products.id',
                'products.name',
                'products.brand',
                'products.sku',
                'products.reorder_level'
            )
            ->havingRaw('total_stock <= products.reorder_level')
            ->get();
            return view('notification.product-form',compact('lowStockProducts'));
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
            return view('notification.expire-product-form',compact('expiredProducts'));
        }

        if ($type === 'request_stock') {
            $id  = $request->id;
            $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
            return view('notification.stock-request-form',compact('stockRequest'));
        }

        if ($type === 'approved_stock') {
            $id  = $request->id;
            $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
            return view('notification.stock-approved-form',compact('stockRequest'));
        }

        
        if ($type === 'transfer_stock') {
            $id  = $request->id;
            $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
            return view('notification.stock-transfer-form',compact('stockRequest'));
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

        $query = Notification::query();

        // **Search filter**
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('type', 'like', '%' . $searchValue . '%')
                  ->orWhere('content', 'like', '%' . $searchValue . '%')
                  ->orWhere('notify_to', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = Notification::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        foreach ($data as $partyUser) {
            
            $records[] = [
                'type' => $partyUser->type,
                'content' => $partyUser->content,
                'notify_to' => $partyUser->notify_to,
                'status' => $partyUser->status,
                'created_at' => Carbon::parse($partyUser->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

}
