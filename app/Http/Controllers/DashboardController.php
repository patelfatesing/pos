<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Inventory;
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

        $data = [
            'store'         => "Selete Store",
            'sales'         => $totalSales + $total_creditpay,
            'products'        => $totalProducts,
            'total_cost_price'     => $inventorySummary->total_cost_price,
            'top_products'  => $totalSales,
            'total_quantity' => $totalQuantity,
            'invoice_count' => $invoice_count
        ];
        return view('dashboard', compact('branch', 'data')); // This refers to resources/views/dashboard.blade.php
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
        $totals = Invoice::selectRaw('
                SUM(total) as total_sales,
                SUM(creditpay) as total_creditpay,
                SUM(total_item_qty) as total_products,
                COUNT(*) as invoice_count
            ')
            ->where('branch_id', $storeId)
            ->whereNotIn('status', ['Hold', 'resumed', 'archived'])
            ->first();

        $totalSales = $totals->total_sales;
        $total_creditpay = $totals->total_creditpay;
        $totalProducts = $totals->total_products;
        $invoice_count = $totals->invoice_count;

        $inventorySummary = \DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(products.cost_price * inventories.quantity) as total_cost_price')
            ->where('inventories.store_id', $storeId)
            ->first();

        $totals_qty = Inventory::selectRaw('SUM(inventories.quantity) as total_quantity')
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.store_id', $storeId)
            ->where('products.is_deleted', 'no')
            ->first();

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
