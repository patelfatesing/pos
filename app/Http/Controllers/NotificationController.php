<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\StockRequest;

class NotificationController extends Controller
{
    // app/Http/Controllers/PopupController.php

    public function loadForm($type,Request $request)
    {
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

}
