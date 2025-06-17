<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get role name from session
        $roleName = strtolower(session('role_name'));
        
        // Redirect non-admin users to items.cart
        if ($roleName !== 'admin') {
         return redirect(route('dashboard'));
        }

        // Only admin users will reach this point
        $branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');

        $totals = Invoice::selectRaw('SUM(total) as total_sales, SUM(total_item_qty) as total_products')->whereNotIn('status',[ 'Hold','resumed','archived'])->first();

        $totalSales = $totals->total_sales;
        $totalProducts = $totals->total_products;

        $inventorySummary = \DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(products.cost_price * inventories.quantity) as total_cost_price')
            ->first();

        $data= [
            'store'         => "Selete Store",
            'sales'         => $totalSales,
            'products'        => $totalProducts,
            'total_cost_price'     => $inventorySummary->total_cost_price,
            'top_products'  => $totalSales,
        ];
        return view('dashboard', compact('branch','data')); // This refers to resources/views/dashboard.blade.php
    }
    public function showStore($storeId)
    {
        $store = Branch::findOrFail($storeId); // or Branch if you're using Branch model
        $data = $this->getDashboardDataForStore($storeId); // Your logic here
        return view('dashboard', compact('store', 'data'));
    }
    protected function getDashboardDataForStore($storeId)
    {
        // Example: Fetch store
        $store = Branch::findOrFail($storeId);

        // Example: Fetch orders/sales for this store
        $totalSales = Invoice::where('branch_id', $storeId)
            // ->whereDate('created_at', today())
             ->sum('total');

        // // Example: Total number of orders today
        $totalProducts = Invoice::where('branch_id', $storeId)
           // ->whereDate('created_at', today())
            ->sum('total_item_qty');

        // // Example: Inventory count for this store


        $inventorySummary = \DB::table('inventories')
        ->join('products', 'inventories.product_id', '=', 'products.id')
        ->where('inventories.store_id', $storeId)
         ->selectRaw('SUM(products.cost_price * inventories.quantity) as total_cost_price')
        ->first();

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

        return [
            'store'         => $store->name,
            'sales'         => $totalSales,
            'products'        => $totalProducts,
            'total_cost_price'     => $inventorySummary->total_cost_price,
            'top_products'  => $totalSales,
        ];
    }

}   
