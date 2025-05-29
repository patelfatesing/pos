<?php

namespace App\Http\Controllers;

use App\Models\DemandOrder;
use App\Models\VendorList;
use App\Models\Product;
use App\Models\DemandOrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class DemandOrderController extends Controller
{
    public function index()
    {
        $demandOrders = DemandOrder::with('vendor')->latest()->paginate(10);
        return view('demand_orders.index', compact('demandOrders'));
    }

    public function create()
    {
        // $vendors = VendorList::all();
        // $products = Product::all();

          // 1. All vendors & products
          $vendors  = VendorList::all();
          $products = Product::all();
  
          // 2. Map product names + size → IDs
          $nameSizeToId = $products->mapWithKeys(function ($product) {
              return [strtolower($product->name . '|' . $product->size) => $product->id];
          })->toArray();
  
          // 3. Find only truly low-stock items, and join categories + sub_categories
          $lowStock = DB::table('inventories')
              ->join('products',        'inventories.product_id', '=', 'products.id')
              ->join('categories',      'products.category_id',   '=', 'categories.id')
              ->join('sub_categories',  'products.subcategory_id','=', 'sub_categories.id')
              ->select([
                  'inventories.product_id',
                  'products.name',
                  'products.size',
                  'categories.name as category_name',
                  'sub_categories.name as subcategory_name',
                  'inventories.quantity as current_stock',
                  'products.reorder_level',
              ])
              ->whereColumn('inventories.quantity', '<', 'products.reorder_level')
              ->get();
  
          $lastWeek  = now()->subDays(7);
          $lastMonth = now()->subDays(30);
  
          // 4. Fetch all invoice items from the last month
          $invoices = DB::table('invoices')
              ->where('created_at', '>=', $lastMonth)
              ->get(['items', 'created_at']);
  
          $weeklySales  = [];
          $monthlySales = [];
  
          foreach ($invoices as $inv) {
              $items = json_decode($inv->items, true);
  
              if (is_array($items)) {
                  foreach ($items as $item) {
                      $key = strtolower(($item['name'] ?? '') . '|' . ($item['size'] ?? ''));
                      $pid = $nameSizeToId[$key] ?? null;
                      if (! $pid) continue;
  
                      $monthlySales[$pid] = ($monthlySales[$pid] ?? 0) + ($item['quantity'] ?? 0);
                      if ($inv->created_at >= $lastWeek) {
                          $weeklySales[$pid] = ($weeklySales[$pid] ?? 0) + ($item['quantity'] ?? 0);
                      }
                  }
              }
          }
  
          // 5. Pending quantities (partially delivered)
          $pending = DemandOrderProduct::where('delivery_status', 'partially')
              ->select('product_id', DB::raw('SUM(quantity - delivery_quantity) as pending_qty'))
              ->groupBy('product_id')
              ->pluck('pending_qty', 'product_id')
              ->toArray();
  
          // 6. Build predictions including category data
          $predictions = $lowStock->map(function($row) use ($weeklySales, $monthlySales, $pending) {
              $pid           = $row->product_id;
              $weeklyCount   = $weeklySales[$pid] ?? 0;
              $monthlyCount  = $monthlySales[$pid] ?? 0;
  
              // Average daily sales = (weekly/7 + monthly/30) / 2
              $avgDailySales = (($weeklyCount / 7) + ($monthlyCount / 30)) / 2;
              $needed        = $row->reorder_level - $row->current_stock;
              $pendingQty    = $pending[$pid] ?? 0;
  
              $suggestedQty  = $needed + ($avgDailySales * 7) - $pendingQty;
  
              return [
                  'product_id'               => $pid,
                  'name'                     => $row->name,
                  'size'                     => $row->size,
                  'category_name'            => $row->category_name,
                  'subcategory_name'         => $row->subcategory_name,
                  'current_stock'            => $row->current_stock,
                  'reorder_level'            => $row->reorder_level,
                  'weekly_sales'             => $weeklyCount,
                  'monthly_sales'            => $monthlyCount,
                  'avg_daily'                => round($avgDailySales, 2),
                  'pending'                  => $pendingQty,
                  'suggested_order_quantity' => max(0, ceil($suggestedQty)),
              ];
          });
  
        return view('demand_orders.step1', compact('vendors', 'products', 'predictions'));
    }

    public function step1()
    {
             
        // $vendors = VendorList::all();
        // $products = Product::all();

          // 1. All vendors & products
          $vendors  = VendorList::all();
          $products = Product::all();
  
        return view('demand_orders.step1', compact('vendors', 'products'));
    }
    public function postStep1(Request $request)
    {
         $validated = $request->validate([
            'purchase_date' => 'required|date',
            'shipping_date' => 'required|date',
            'avg_sales' => 'required|numeric',
            'vendor_id' => 'required',
        ]);
        $demand_date=$request->purchase_date;
         // 1. All vendors & products
        $vendors  = VendorList::all();
        $products = Product::all();

        // 2. Map product names + size → IDs
        $nameSizeToId = $products->mapWithKeys(function ($product) {
            return [strtolower($product->name . '|' . $product->size) => $product->id];
        })->toArray();

        $lowStock = getProductStockQuery()
        ->whereColumn('inventories.quantity', '<', 'products.reorder_level')
        ->get();

        $lastWeek  = now()->subDays($request->avg_sales);
       // $lastMonth = now()->subDays(30);
        // 4. Fetch all invoice items from the last month
        $invoices = DB::table('invoices')
            ->where('created_at', '>=', $lastWeek)
            ->get(['items', 'created_at']);

        $weeklySales  = [];
        $monthlySales = [];

        foreach ($invoices as $inv) {
            $items = json_decode($inv->items, true);

            if (is_array($items)) {
                foreach ($items as $item) {
                    $key = strtolower(($item['name'] ?? '') . '|' . ($item['size'] ?? ''));
                    $pid = $nameSizeToId[$key] ?? null;
                    if (! $pid) continue;

                    $monthlySales[$pid] = ($monthlySales[$pid] ?? 0) + ($item['quantity'] ?? 0);
                    if ($inv->created_at >= $lastWeek) {
                        $weeklySales[$pid] = ($weeklySales[$pid] ?? 0) + ($item['quantity'] ?? 0);
                    }
                }
            }
        }

        // 5. Pending quantities (partially delivered)
        $pending = DemandOrderProduct::where('delivery_status', 'partially')
            ->select('product_id', DB::raw('SUM(quantity - delivery_quantity) as pending_qty'))
            ->groupBy('product_id')
            ->pluck('pending_qty', 'product_id')
            ->toArray();

        // 6. Build predictions including category data
        $predictions = $lowStock->map(function($row) use ($weeklySales, $request, $pending) {
            $pid           = $row->product_id;
            $weeklyCount   = $weeklySales[$pid] ?? 0;
            $monthlyCount  = $monthlySales[$pid] ?? 0;

            // Average daily sales = (weekly/7 + monthly/30) / 2
            $avgDailySales = $weeklyCount / $request->avg_sales / 2;
            $needed        = $row->reorder_level - $row->current_stock;
            $pendingQty    = $pending[$pid] ?? 0;

            $suggestedQty  = $needed + ($avgDailySales * $request->avg_sales) - $pendingQty;

            return [
                'product_id'               => $pid,
                'name'                     => $row->name,
                'size'                     => $row->size,
                'category_name'            => $row->category_name,
                'subcategory_name'         => $row->subcategory_name,
                'current_stock'            => $row->current_stock,
                'reorder_level'            => $row->reorder_level,
                'weekly_sales'             => $weeklyCount,
                'monthly_sales'            => $monthlyCount,
                'avg_daily'                => round($avgDailySales, 2),
                'pending'                  => $pendingQty,
                'suggested_order_quantity' => max(0, ceil($suggestedQty)),
            ];
        });
        session(['demand_orders.step1' => $validated]);
        // 7. Return to view
       // pre($predictions,1);
       
        return view('demand_orders.step2', compact('vendors', 'products', 'predictions','demand_date'));
        // Store data in session

        //return redirect()->route('demand-order.step2');
    }
   
   public function postStep2(Request $request)
    {
        if (!$request->has('selected') || count($request->input('selected')) === 0) {
             if (session()->has('demand_orders.step2')) {
                session()->forget('demand_orders.step2');
            }
            // Redirect to 'selected.form' route with error flash message
            return redirect()->route('demand-order.step2')->with('error', 'Please select at least one product.');
        }

        // $orderQty = $request->input('order_qty'); // This will be an array keyed by product_id
        $validated = $request->validate([
            'selected' => 'required',
            'order_qty' => 'required',
        ]);

        $step2Data = session('demand_orders.step1');
        $demand_date=$step2Data['purchase_date'];
          $selectedProducts = [];

        foreach ($request->selected as $productId) {
            $qty = $request->order_qty[$productId] ?? 0;

            // Skip if qty is zero
            if ($qty <= 0) {
                continue;
            }

            // Optional: fetch product info
            $PurchaseController = new PurchaseController();
            $productDetails = $PurchaseController->getProductDetails($productId);
            $productDetailsAry=(array) $productDetails;
            $productDetailsAry['amount']=$productDetails->cost_price*$qty;
            $selectedProducts[] = [
                'product_id' => $productId,
                'order_qty' => $qty,
                'product_details' => $productDetailsAry, // optional
            ];
        }
        $products = Product::all();
        // Store data in session
        session(['demand_orders.step2' => $validated]);

        return view('demand_orders.step3', compact( 'products','selectedProducts','demand_date'));
    }

    public function postStep3(Request $request)
    {
        
        $data = $request->all();
        session(['demand_orders.step3' => $data]);

        $user=auth()->user();
        //
        $filename = 'demand_' . time() . '.pdf';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('demand', ['data' => $data,'user'=>$user]);
        $pdfPath = storage_path('app/public/demand/'.$filename);
        $pdf->save($pdfPath);
        if (session()->has('demand_orders.step1')) {
        session()->forget('demand_orders.step1');
        }
        session(['demand_orders.step3' => $data]);
        if (session()->has('demand_orders.step2')) {
        session()->forget('demand_orders.step2');
        }
        session(['demand_orders.step3' => $data]);
        if (session()->has('demand_orders.step4')) {
        session()->forget('demand_orders.step4');
        }
        return view('demand_orders.step4', ['pdfPath' =>  asset('storage/demand/' . $filename),'user'=>$user]);

    }
    public function step2()
    {
        $step2Data = session('demand_orders.step1');
        // $vendors = VendorList::all();
        // $products = Product::all();

          // 1. All vendors & products
          $vendors  = VendorList::all();
          $products = Product::all();
  
          // 2. Map product names + size → IDs
          $nameSizeToId = $products->mapWithKeys(function ($product) {
              return [strtolower($product->name . '|' . $product->size) => $product->id];
          })->toArray();
  
          // 3. Find only truly low-stock items, and join categories + sub_categories
        $lowStock = getProductStockQuery()
        ->whereColumn('inventories.quantity', '<', 'products.reorder_level')
        ->get();

  
          $lastWeek  = now()->subDays($step2Data['avg_sales']);
          //$lastMonth = now()->subDays(30);
  
          // 4. Fetch all invoice items from the last month
          $invoices = DB::table('invoices')
              ->where('created_at', '>=', $lastWeek)
              ->get(['items', 'created_at']);
  
          $weeklySales  = [];
          $monthlySales = [];
  
          foreach ($invoices as $inv) {
              $items = json_decode($inv->items, true);
  
              if (is_array($items)) {
                  foreach ($items as $item) {
                      $key = strtolower(($item['name'] ?? '') . '|' . ($item['size'] ?? ''));
                      $pid = $nameSizeToId[$key] ?? null;
                      if (! $pid) continue;
  
                      //$monthlySales[$pid] = ($monthlySales[$pid] ?? 0) + ($item['quantity'] ?? 0);
                      if ($inv->created_at >= $lastWeek) {
                          $weeklySales[$pid] = ($weeklySales[$pid] ?? 0) + ($item['quantity'] ?? 0);
                      }
                  }
              }
          }
  
          // 5. Pending quantities (partially delivered)
          $pending = DemandOrderProduct::where('delivery_status', 'partially')
              ->select('product_id', DB::raw('SUM(quantity - delivery_quantity) as pending_qty'))
              ->groupBy('product_id')
              ->pluck('pending_qty', 'product_id')
              ->toArray();
  
          // 6. Build predictions including category data
          $predictions = $lowStock->map(function($row) use ($weeklySales, $step2Data, $pending) {
              $pid           = $row->product_id;
              $weeklyCount   = $weeklySales[$pid] ?? 0;
              $monthlyCount  = $monthlySales[$pid] ?? 0;
  
              $avgDailySales = ($weeklyCount / $step2Data['avg_sales']) / 2;
              $needed        = $row->reorder_level - $row->current_stock;
              $pendingQty    = $pending[$pid] ?? 0;
  
              $suggestedQty  = $needed + ($avgDailySales * $step2Data['avg_sales']) - $pendingQty;
              return [
                  'product_id'               => $pid,
                  'name'                     => $row->name,
                  'size'                     => $row->size,
                  'category_name'            => $row->category_name,
                  'subcategory_name'         => $row->subcategory_name,
                  'current_stock'            => $row->current_stock,
                  'reorder_level'            => $row->reorder_level,
                  'weekly_sales'             => $weeklyCount,
                  'monthly_sales'            => $monthlyCount,
                  'avg_daily'                => round($avgDailySales, 2),
                  'pending'                  => $pendingQty,
                  'suggested_order_quantity' => max(0, ceil($suggestedQty)),
              ];
          });
          
        return view('demand_orders.step2', compact('vendors', 'products', 'predictions'));
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
        // 1. All vendors & products
        $vendors  = VendorList::all();
        $products = Product::all();

        // 2. Map product names + size → IDs
        $nameSizeToId = $products->mapWithKeys(function ($product) {
            return [strtolower($product->name . '|' . $product->size) => $product->id];
        })->toArray();

        // 3. Find only truly low-stock items, and join categories + sub_categories
        $lowStock = DB::table('inventories')
            ->join('products',        'inventories.product_id', '=', 'products.id')
            ->join('categories',      'products.category_id',   '=', 'categories.id')
            ->join('sub_categories',  'products.subcategory_id','=', 'sub_categories.id')
            ->select([
                'inventories.product_id',
                'products.name',
                'products.size',
                'categories.name as category_name',
                'sub_categories.name as subcategory_name',
                'inventories.quantity as current_stock',
                'products.reorder_level',
            ])
            ->whereColumn('inventories.quantity', '<', 'products.reorder_level')
            ->get();

        $lastWeek  = now()->subDays(7);
        $lastMonth = now()->subDays(30);

        // 4. Fetch all invoice items from the last month
        $invoices = DB::table('invoices')
            ->where('created_at', '>=', $lastMonth)
            ->get(['items', 'created_at']);

        $weeklySales  = [];
        $monthlySales = [];

        foreach ($invoices as $inv) {
            $items = json_decode($inv->items, true);

            if (is_array($items)) {
                foreach ($items as $item) {
                    $key = strtolower(($item['name'] ?? '') . '|' . ($item['size'] ?? ''));
                    $pid = $nameSizeToId[$key] ?? null;
                    if (! $pid) continue;

                    $monthlySales[$pid] = ($monthlySales[$pid] ?? 0) + ($item['quantity'] ?? 0);
                    if ($inv->created_at >= $lastWeek) {
                        $weeklySales[$pid] = ($weeklySales[$pid] ?? 0) + ($item['quantity'] ?? 0);
                    }
                }
            }
        }

        // 5. Pending quantities (partially delivered)
        $pending = DemandOrderProduct::where('delivery_status', 'partially')
            ->select('product_id', DB::raw('SUM(quantity - delivery_quantity) as pending_qty'))
            ->groupBy('product_id')
            ->pluck('pending_qty', 'product_id')
            ->toArray();

        // 6. Build predictions including category data
        $predictions = $lowStock->map(function($row) use ($weeklySales, $monthlySales, $pending) {
            $pid           = $row->product_id;
            $weeklyCount   = $weeklySales[$pid] ?? 0;
            $monthlyCount  = $monthlySales[$pid] ?? 0;

            // Average daily sales = (weekly/7 + monthly/30) / 2
            $avgDailySales = (($weeklyCount / 7) + ($monthlyCount / 30)) / 2;
            $needed        = $row->reorder_level - $row->current_stock;
            $pendingQty    = $pending[$pid] ?? 0;

            $suggestedQty  = $needed + ($avgDailySales * 7) - $pendingQty;

            return [
                'product_id'               => $pid,
                'name'                     => $row->name,
                'size'                     => $row->size,
                'category_name'            => $row->category_name,
                'subcategory_name'         => $row->subcategory_name,
                'current_stock'            => $row->current_stock,
                'reorder_level'            => $row->reorder_level,
                'weekly_sales'             => $weeklyCount,
                'monthly_sales'            => $monthlyCount,
                'avg_daily'                => round($avgDailySales, 2),
                'pending'                  => $pendingQty,
                'suggested_order_quantity' => max(0, ceil($suggestedQty)),
            ];
        });

        // 7. Return to view
        return view('demand_orders.createPre', compact('vendors', 'products', 'predictions'));
    }

    public function show(DemandOrder $demandOrder)
    {
        $demandOrder->load('products');
        return view('demand_orders.show', compact('demandOrder'));
    }
}
