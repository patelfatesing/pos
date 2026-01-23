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
        $demandOrders = DemandOrder::with('vendor')->with('products')->latest()->paginate(10);
        return view('demand_orders.index', compact('demandOrders'));
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

        // Base query with join for vendor name
        $query = DemandOrder::with(['vendor', 'products'])
            ->leftJoin('vendor_lists', 'vendor_lists.id', '=', 'demand_orders.vendor_id')
            ->select('demand_orders.*');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('vendor_lists.name', 'like', '%' . $searchValue . '%');
            });
        }

        // Total before filtering
        $recordsTotal = DemandOrder::count();

        // Total after filtering
        $recordsFiltered = $query->count();

        // Apply ordering and pagination
        if (!empty($orderColumn)) {
            $query->orderBy('demand_orders.' . $orderColumn, $orderDirection);
        }
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

         $roleId = auth()->user()->role_id;
        
        $userId = auth()->id();

        $listAccess = getAccess($roleId, 'demand-order-manage');

        // ❌ No permission → return empty table
        if (in_array($listAccess, ['none', 'no'])) {
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        // 👤 Own permission → only own products
        if ($listAccess === 'own') {
            $query->where('created_by', $userId);
        }

        // Fetch results
        $data = $query->get();

        // Build response data
        $records = [];
        foreach ($data as $order) {
            // $ownerId = $order->created_by;  
            $vendorName = $order->vendor->name ?? 'N/A';
            $dataTotal = $this->getTotal($order->id); // Assume it returns object with total_quantity, total_sell_price
            //  if (canDo($roleId, 'product-edit', $ownerId)) {
            //  }
            $action = '<div class="d-flex align-items-center list-action">
            <button class="btn btn-warning btn-sm" onclick="openPDF(\'' . asset('storage/demand/' . $order->file_name) . '\')" data-toggle="modal" data-target="#pdfModal">
                <i class="las la-file-pdf"></i> View File
            </button>
            <a href="' . route('demand-order.view', $order->id) . '" class="btn btn-info btn-sm ml-2">View</a>
        </div>';

            $records[] = [
                'status' => ucfirst($order->status),
                'shipping_date' => $order->shipping_date,
                'purchase_date' => $order->purchase_date,
                'total_quantity' => $dataTotal->total_quantity ?? 0,
                'total_sell_price' => $dataTotal->total_sell_price ?? 0,
                'sub_category' => $this->getSubCategory($order->id),
                'vendor' => $vendorName,
                'action' => $action,
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
        ]);
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

        return view('demand_orders.step1', compact('vendors', 'products', 'categories'));
    }

    public function view($id)
    {
        $demandOrderProducts = \DB::table('demand_order_products')
            ->join('demand_orders', 'demand_order_products.demand_order_id', '=', 'demand_orders.id')
            ->join('products', 'demand_order_products.product_id', '=', 'products.id')
            ->where('demand_order_products.demand_order_id', $id)
            ->select(
                'demand_order_products.id',
                'demand_order_products.demand_order_id',
                'demand_order_products.product_id',
                'products.name as product_name',
                'demand_order_products.quantity',
                'demand_order_products.barcode',
                'demand_order_products.mrp',
                'demand_order_products.rate',
                'demand_order_products.sell_price',
                'demand_order_products.delivery_status',
                'demand_order_products.delivery_quantity',
                'demand_order_products.created_at',
                'demand_order_products.updated_at'
            )
            ->orderBy('demand_order_products.created_at', 'desc')
            ->paginate(15);

        return view('demand_orders.product', compact('demandOrderProducts'));
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
            ->where('is_deleted', 'no')
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
            $productDetailsAry['amount'] = @$productDetails->cost_price * $qty;
            $selectedProducts[] = [
                'product_id' => $productId,
                'order_qty' => $qty,
                'pack_size' => $pack_size,
                'product_details' => $productDetailsAry, // optional
            ];
        }
        $products = Product::query()
            ->when(!empty($step2Data['category_id']), function ($query) use ($request, $step2Data) {
                $query->where('category_id', $step2Data['category_id']);
            })
            ->when(!empty($request->subcategory_id), function ($query) use ($request, $step2Data) {
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
            'file_name' =>  $filename,
        ]);

        foreach ($data['products'] as $product) {
            DemandOrderProduct::create([
                'demand_order_id' => $demandOrder->id,
                'product_id' => $product['product_id'],
                'quantity' => $product['qnt'],
                'barcode' => $product['barcode'] ?? null,
                'mrp' => $product['mrp'] ?? 0,
                'rate' => $product['rate'] ?? 0,
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

    public function getTotal($demandOrderId)
    {
        $totals = DemandOrderProduct::where('demand_order_id', $demandOrderId)
            ->selectRaw('SUM(quantity) as total_quantity, SUM(sell_price) as total_sell_price')
            ->first();
        // dd($totals);
        return $totals;
    }

    public function getSubCategory($p_id)
    {
        $product_data = DemandOrderProduct::select('product_id')->where('demand_order_id', $p_id)->first();
        $product = Product::with('subcategory')->find($product_data->product_id);

        return $product && $product->subcategory ? $product->subcategory->name : 'N/A';
    }
}
