<?php

namespace App\Http\Controllers;

use App\Models\DemandOrder;
use App\Models\VendorList;
use App\Models\Product;
use App\Models\DemandOrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\SubCategory;


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
            ->join('sub_categories',  'products.subcategory_id', '=', 'sub_categories.id')
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
        $predictions = $lowStock->map(function ($row) use ($weeklySales, $monthlySales, $pending) {
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
        $categories = Category::all();

        return view('demand_orders.step1', compact('vendors', 'products','categories'));
    }
    public function postStep1(Request $request)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'shipping_date' => 'required|date',
            'avg_sales' => 'required',
            'vendor_id' => 'required',
            'category_id' => 'nullable',
            'subcategory_id' => 'nullable',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
        ]);
        $demand_date = $request->purchase_date;
        // 1. All vendors & products
        $vendors  = VendorList::all();
        $products = Product::query()
        ->when(!empty($request->category_id), function ($query) use ($request) {
            $query->where('category_id', $request->category_id);
        })
        ->when(!empty($request->subcategory_id), function ($query) use ($request) {
            $query->where('subcategory_id', $request->subcategory_id);
        })
        ->get();
        // 2. Map product names + size → IDs
        $nameSizeToId = $products->mapWithKeys(function ($product) {
            return [$product->id => $product->name];
        })->toArray();
      $lowStock = getProductStockQuery()
        ->whereColumn('inventories.quantity', '<', 'products.reorder_level')
        ->when($request->filled('category_id'), function ($query) use ($request) {
            return $query->where('products.category_id', $request->category_id);
        })
        ->when($request->filled('subcategory_id'), function ($query) use ($request) {
            return $query->where('products.subcategory_id', $request->subcategory_id);
        })
        ->get();

        $startDate = Carbon::parse(@$request->input('start_date')); // e.g. "2025-05-21"
        $endDate = Carbon::parse(@$request->input('end_date'));     // e.g. "2025-05-30"

        $diffInDays = $startDate->diffInDays($endDate);
        if (!empty($diffInDays) && !empty($request->input('start_date')) && !empty($request->input('end_date'))) {
            $lastWeek  = now()->subDays($diffInDays);
            $selectedAvg = $diffInDays;
        } else {
            $lastWeek  = now()->subDays($request->avg_sales);
            $selectedAvg = $request->avg_sales;
        }
        $lastWeek = $lastWeek->toDateTimeString();

        // $lastMonth = now()->subDays(30);
        // 4. Fetch all invoice items from the last month
        // $invoices = DB::table('invoices')
        //     ->where('created_at', '>=', $lastWeek)
        //     ->get(['items', 'created_at']);
        $invoices = DB::table('invoices')
            ->join('branches', 'invoices.branch_id', '=', 'branches.id') // assuming branch_id in invoices
            ->where('invoices.created_at', '>=', $lastWeek)
            ->where('invoices.status', "paid")
            ->where('branches.is_warehouser', 'yes')
            ->get(['invoices.items', 'invoices.created_at']);

        $weeklySales  = [];
        $monthlySales = [];

        $categories = Category::find($request->category_id);
        $subCategory = SubCategory::find($request->subcategory_id);

        foreach ($invoices as $inv) {
            $items = json_decode($inv->items, true);

            if (is_array($items)) {
                foreach ($items as $item) {
                    $match = true;

                    // Check category match if filter is applied
                    if (!empty($request->category_id)) {
                        if (!isset($item['category']) || $item['category'] != $categories->name) {
                            $match = false;
                        }
                    }

                    // Check subcategory match if filter is applied
                    if (!empty($request->subcategory_id)) {
                        if (!isset($item['subcategory']) || $item['subcategory'] != $subCategory->name) {
                            $match = false;
                        }
                    }

                    if (!$match) {
                        continue;
                    }

                    if (!isset($nameSizeToId[$item['product_id']])) {
                        continue; // Skip if product_id not mapped
                    }

                    if ($inv->created_at >= $lastWeek) {
                        $weeklySales[$item['product_id']] = ($weeklySales[$item['product_id']] ?? 0) + ($item['quantity'] ?? 0);
                    }
                }
            }
        }


        // 5. Pending quantities (partially delivered)
        $pending = DemandOrderProduct::query()
            ->where('delivery_status', 'partially')
            ->join('products', 'demand_order_products.product_id', '=', 'products.id')
            ->when(!empty($request->category_id), function ($query) use ($request) {
            $query->where('products.category_id', $request->category_id);
            })
            ->when(!empty($request->subcategory_id), function ($query) use ($request) {
            $query->where('products.subcategory_id', $request->subcategory_id);
            })
            ->select('demand_order_products.product_id', DB::raw('SUM(quantity - delivery_quantity) as pending_qty'))
            ->groupBy('demand_order_products.product_id')
            ->pluck('pending_qty', 'demand_order_products.product_id')
            ->toArray();

        // 6. Build predictions including category data
        $predictions = $lowStock->map(function ($row) use ($weeklySales, $selectedAvg, $pending) {
            $pid           = $row->product_id;
            $weeklyCount   = $weeklySales[$pid] ?? 0;
            $monthlyCount  = $monthlySales[$pid] ?? 0;

            // Average daily sales = (weekly/7 + monthly/30) / 2
            $avgDailySales = $weeklyCount / $selectedAvg / 2;
            $needed        = $row->reorder_level - $row->current_stock;
            $pendingQty    = $pending[$pid] ?? 0;
            //dd($needed,$avgDailySales,$selectedAvg,$pendingQty);
            $suggestedQty  = $needed + ($avgDailySales * $selectedAvg) - $pendingQty;

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

        return view('demand_orders.step2', compact('vendors', 'products', 'predictions', 'demand_date'));
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
        $demand_date = $step2Data['purchase_date'];
        $selectedProducts = [];

        foreach ($request->selected as $productId) {
            $qty = $request->order_qty[$productId] ?? 0;
            $pack_size = $request->pack_size[$productId] ?? 0;

            // Skip if qty is zero
            if ($qty <= 0) {
                continue;
            }

            // Optional: fetch product info
            $PurchaseController = new PurchaseController();
            $productDetails = $PurchaseController->getProductDetails($productId);
            $productDetailsAry = (array) $productDetails;
            $productDetailsAry['amount'] = $productDetails->cost_price * $qty;
            $selectedProducts[] = [
                'product_id' => $productId,
                'order_qty' => $qty,
                 'pack_size' => $pack_size,
                'product_details' => $productDetailsAry, // optional
            ];
        }
         $products = Product::query()
        ->when(!empty($step2Data['category_id']), function ($query) use ($request,$step2Data) {
            $query->where('category_id', $step2Data['category_id']);
        })
        ->when(!empty($request->subcategory_id), function ($query) use ($request,$step2Data) {
            $query->where('subcategory_id', $step2Data['subcategory_id']);
        })
        ->get();
        // Store data in session
        session(['demand_orders.step2' => $validated]);

        return view('demand_orders.step3', compact('products', 'selectedProducts', 'demand_date'));
    }

    public function postStep3(Request $request)
    {

        $data = $request->all();
        session(['demand_orders.step3' => $data]);

        $user = auth()->user();
        //
        $filename = 'demand_' . time() . '.pdf';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('demand', ['data' => $data, 'user' => $user]);
        $pdfPath = storage_path('app/public/demand/' . $filename);
        $pdf->save($pdfPath);
        $step2Data = session('demand_orders.step1');
        $my2 = session('demand_orders.step2');
        $my3 = session('demand_orders.step3');
        $demandOrder = DemandOrder::create([
            'vendor_id' => $step2Data['vendor_id'],
            'purchase_date' => $step2Data['purchase_date'],
            //'purchase_order_no' =>2,
            'shipping_date' => $step2Data['shipping_date'],
            'notes' => $my3['notes'],
            'status' =>  'order',
        ]);

        foreach ($data['products'] as $product) {
            DemandOrderProduct::create([
                'demand_order_id' => $demandOrder->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['qnt'],
                'barcode' => $product['barcode'] ?? null,
                'mrp' => $product['mrp'],
                'rate' => $product['rate'],
                'sell_price' => $product['amount'],
                'delivery_status' => 'partially',
                'delivery_quantity' => 0,
            ]);
        }

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
        return view('demand_orders.step4', ['pdfPath' =>  asset('storage/demand/' . $filename), 'user' => $user]);
    }
    public function step2()
    {
        $step2Data = session('demand_orders.step1');
        // $vendors = VendorList::all();
        // $products = Product::all();

        // 1. All vendors & products
        $vendors  = VendorList::all();
        $products = Product::query()
        ->when(!empty($step2Data['category_id']), function ($query) use ($step2Data) {
            $query->where('category_id', $step2Data['category_id']);
        })
        ->when(!empty($step2Data['subcategory_id']), function ($query) use ($step2Data) {
            $query->where('subcategory_id', $step2Data['subcategory_id']);
        })
        ->get();

        // 2. Map product names + size → IDs
        $nameSizeToId = $products->mapWithKeys(function ($product) {
            return [$product->id => $product->name];
        })->toArray();
        
        // 3. Find only truly low-stock items, and join categories + sub_categories
        $lowStock = getProductStockQuery()
        ->whereColumn('inventories.quantity', '<', 'products.reorder_level')
         ->when(!empty($step2Data['category_id']), function ($query) use ($step2Data) {
            $query->where('products.category_id', $step2Data['category_id']);
        })
        ->when(!empty($step2Data['subcategory_id']), function ($query) use ($step2Data) {
            $query->where('products.subcategory_id', $step2Data['subcategory_id']);
        })
        ->get();

        $startDate = Carbon::parse(@$step2Data['start_date']); // e.g. "2025-05-21"
        $endDate = Carbon::parse(@$step2Data['end_date']);     // e.g. "2025-05-30"

        $diffInDays = $startDate->diffInDays($endDate);
        if (!empty($diffInDays) && !empty($step2Data['start_date']) && !empty($step2Data['start_date'])) {
            $lastWeek  = now()->subDays($diffInDays);
            $selectedAvg = $diffInDays;
        } else {
            $lastWeek  = now()->subDays($step2Data['avg_sales']);
            $selectedAvg = $step2Data['avg_sales'];
        }
        //$lastMonth = now()->subDays(30);

        // 4. Fetch all invoice items from the last month
        $invoices = DB::table('invoices')
            ->join('branches', 'invoices.branch_id', '=', 'branches.id') // assuming branch_id in invoices
            ->where('invoices.created_at', '>=', $lastWeek)
            ->where('invoices.status', "paid")
            ->where('branches.is_warehouser', 'yes')
            ->get(['invoices.items', 'invoices.created_at']);

        $weeklySales  = [];
        $monthlySales = [];

        $categories = Category::find($step2Data['category_id'] ?? "");
        $subCategory = SubCategory::find($step2Data['subcategory_id'] ?? "");

        foreach ($invoices as $inv) {
            $items = json_decode($inv->items, true);

            if (is_array($items)) {
                foreach ($items as $item) {
                    $match = true;

                    // Check category match if filter is applied
                    if (!empty($step2Data['category_id'])) {
                        if (!isset($item['category']) || $item['category'] != $categories->name) {
                            $match = false;
                        }
                    }

                    // Check subcategory match if filter is applied
                    if (!empty($step2Data['subcategory_id'])) {
                        if (!isset($item['subcategory']) || $item['subcategory'] != $subCategory->name) {
                            $match = false;
                        }
                    }

                    if (!$match) {
                        continue;
                    }

                    if (!isset($nameSizeToId[$item['product_id']])) {
                        continue; // Skip if product_id not mapped
                    }

                    if ($inv->created_at >= $lastWeek) {
                        $weeklySales[$item['product_id']] = ($weeklySales[$item['product_id']] ?? 0) + ($item['quantity'] ?? 0);
                    }
                }
            }
        }

        // 5. Pending quantities (partially delivered)
        $pending = DemandOrderProduct::query()
            ->where('delivery_status', 'partially')
            ->join('products', 'demand_order_products.product_id', '=', 'products.id')
            ->when(!empty($step2Data['category_id']), function ($query) use ($step2Data) {
            $query->where('products.category_id', $step2Data['category_id']);
            })
            ->when(!empty($step2Data['subcategory_id']), function ($query) use ($step2Data) {
            $query->where('products.subcategory_id', $step2Data['subcategory_id']);
            })
            ->select('demand_order_products.product_id', DB::raw('SUM(quantity - delivery_quantity) as pending_qty'))
            ->groupBy('demand_order_products.product_id')
            ->pluck('pending_qty', 'demand_order_products.product_id')
            ->toArray();
        
        // 6. Build predictions including category data
        $predictions = $lowStock->map(function ($row) use ($weeklySales, $selectedAvg, $pending) {

            $pid           = $row->product_id;
            $weeklyCount   = $weeklySales[$pid] ?? 0;
            $monthlyCount  = $monthlySales[$pid] ?? 0;

            $avgDailySales = ($weeklyCount / $selectedAvg) / 2;
            $needed        = $row->reorder_level - $row->current_stock;
            $pendingQty    = $pending[$pid] ?? 0;

            $suggestedQty  = $needed + ($avgDailySales * $selectedAvg) - $pendingQty;
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
            ->join('sub_categories',  'products.subcategory_id', '=', 'sub_categories.id')
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
        $predictions = $lowStock->map(function ($row) use ($weeklySales, $monthlySales, $pending) {
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
