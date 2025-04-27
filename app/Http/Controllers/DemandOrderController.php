<?php

namespace App\Http\Controllers;

use App\Models\DemandOrder;
use App\Models\VendorList;
use App\Models\Product;
use App\Models\DemandOrderProduct;
use Illuminate\Http\Request;

class DemandOrderController extends Controller
{
    public function index()
    {
        $demandOrders = DemandOrder::with('vendor')->latest()->paginate(10);
        return view('demand_orders.index', compact('demandOrders'));
    }

    public function create()
    {
        $vendors = VendorList::all();
        $products = Product::all();
        return view('demand_orders.create', compact('vendors', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendor_lists,id',
            'purchase_date' => 'required|date',
            'purchase_order_no' => 'required|unique:demand_orders,purchase_order_no',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $demandOrder = DemandOrder::create([
            'vendor_id' => $request->vendor_id,
            'purchase_date' => $request->purchase_date,
            'purchase_order_no' => $request->purchase_order_no,
            'shipping_date' => $request->shipping_date,
            'notes' => $request->notes,
            'status' => $request->status ?? 'order',
        ]);

        foreach ($request->products as $product) {
            DemandOrderProduct::create([
                'demand_order_id' => $demandOrder->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'barcode' => $product['barcode'] ?? null,
                'mrp' => $product['mrp'],
                'rate' => $product['rate'],
                'sell_price' => $product['sell_price'],
                'delivery_status' => 'partially',
                'delivery_quantity' => 0,
            ]);
        }

        return redirect()->route('demand_orders.index')->with('success', 'Demand Order Created Successfully!');
    }

    public function createPrediction()
    {
     
        $vendors = VendorList::all();
        $products = Product::all();

        $lowStockProducts = Product::whereColumn('stock_quantity', '<', 'reorder_level')->get();

        $lastWeek = now()->subDays(7);
        $lastMonth = now()->subDays(30);

        $weeklySales = DB::table('invoices')
            ->select('product_id', DB::raw('SUM(quantity) as total'))
            ->where('created_at', '>=', $lastWeek)
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $monthlySales = DB::table('invoices')
            ->select('product_id', DB::raw('SUM(quantity) as total'))
            ->where('created_at', '>=', $lastMonth)
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $pendingProducts = DemandOrderProduct::where('delivery_status', 'partially')
            ->select('product_id', DB::raw('SUM(quantity - delivery_quantity) as pending_qty'))
            ->groupBy('product_id')
            ->pluck('pending_qty', 'product_id');

        return view('demand_orders.createPre', compact(
            'vendors', 'products', 'lowStockProducts', 'weeklySales', 'monthlySales', 'pendingProducts'
        ));
    }

    public function show(DemandOrder $demandOrder)
    {
        $demandOrder->load('products');
        return view('demand_orders.show', compact('demandOrder'));
    }
}
