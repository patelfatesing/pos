<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockRequest;
use App\Models\StockRequestItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\User;
use App\Models\StockTransfer;
use Illuminate\Support\Str;
use App\Models\ShiftClosing;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = Inventory::with('product')->get();
        return view('stocks.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function add()
    {
        $stores = Branch::where('is_deleted', 'no')->get();
        $products = Product::all();
        $data = User::with('userInfo')
            ->where('users.id', Auth::id())
            ->where('is_deleted', 'no')
            ->firstOrFail();

        return view('stocks.create', compact('stores', 'products', 'data'));
    }

    public function addWarehouse()
    {
        $stores = Branch::where('is_deleted', 'no')->get();
        $products = Product::all();

        return view('stocks.create_warehouse', compact('stores', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeWarehouse(Request $request)
    {

        $validated = $request->validate([
            'store_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'store_id.required' => 'Please select the source store.',
            'items.required' => 'At least one product is required.',
            'items.*.product_id.required' => 'Please select a product.',
            'items.*.quantity.required' => 'Please enter the quantity.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ]);
        DB::beginTransaction();
        $data = User::with('userInfo')
            ->where('users.id', Auth::id())
            ->where('is_deleted', 'no')
            ->firstOrFail();

        $store_id = $request->store_id;

        $branch_id = $data->userInfo->branch_id;

        $stockRequest = StockRequest::create([
            'store_id' => $store_id,
            'requested_by' => 1,
            'notes' => $request->notes,
            'requested_at' => now(),
            'created_by' => Auth::id(),
        ]);

        $totalProductCount = 0;
        $totalQuantitySum = 0;
        $uniqueProductIds = [];
        foreach ($request['items']  as $key =>  $item) {
            $productId = $item['product_id'];
            $totalQty = (int) $item['quantity'];
            $inventories = Inventory::where('product_id', $productId)
                ->where('store_id', $request->store_id)
                ->orderBy('expiry_date')
                ->get();

            $totalQuantity = (int)$inventories->sum('quantity');
            if ($totalQuantity < $totalQty) {
                $errors["items.$key.quantity"] = "Insufficient stock in source store. Available: $totalQuantity";
            }
            // $branches = $item['branches'] ?? [];
            // $quantities = $item['branch_quantities'] ?? [];

            // Filter out unchecked branches
            // $branchQuantities = [];
            // foreach ($branches as $branch => $checked) {
            //     if (isset($quantities[$branch])) {
            //         $branchQuantities[$branch] = (int) $quantities[$branch];
            //     }
            // }

            // Optional: validate that branch total matches or doesn't exceed total
            $sum = $totalQty;
            // if ($sum > $totalQty) {
            //     return back()->withErrors(['items' => "Total quantity for product ID $productId is less than sum of branch quantities."])->withInput();
            // }

            // Track unique product and sum quantity
            // if ($sum > 0) {
            if (!in_array($productId, $uniqueProductIds)) {
                $uniqueProductIds[] = $productId;
            }
            $totalQuantitySum += $sum;
            // }

            // foreach ($branches as $branch => $checked) {

            //     $quantity = $item['branch_quantities'][$branch] ?? null;

            StockRequestItem::create([
                'request_to_location_id' => $store_id,
                'stock_request_id' => $stockRequest->id,
                'product_id' => $productId,
                'quantity' => $totalQty
            ]);
            // }

        }
        if ($request->ajax() && !empty($errors)) {
            DB::rollback();
            return response()->json(['errors' => $errors], 422);
        }
        // 🔄 Update totals
        $stockRequest->update([
            'total_product' => count($uniqueProductIds),
            'total_quantity' => $totalQuantitySum
        ]);
        DB::commit();

        //  return redirect()->route('items.cart')->with('success', 'Stock request submitted successfully.');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'store_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {

            $data = User::with('userInfo')
                ->where('users.id', Auth::id())
                ->where('is_deleted', 'no')
                ->firstOrFail();


            $branch_id = $data->userInfo->branch_id;


            $stockRequest = StockRequest::create([
                'store_id' => 1,
                'requested_by' => $branch_id,
                'notes' => $request->notes,
                'requested_at' => now(),
                'created_by' => Auth::id(),
                // 'total_request_quantity' => $request->total_request_quantity,
            ]);

            foreach ($validated['items'] as $item) {
                StockRequestItem::create([
                    'stock_request_id' => $stockRequest->id,
                    'request_to_location_id' => 1,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            $items = $request->input('items');
            // Total distinct products
            $total_product = count($items);

            // Total quantity
            $total_quantity = collect($items)->sum('quantity');
            // 🔄 Update totals

            $stockRequest->update([
                'total_product' => $total_product,
                'total_quantity' => $total_quantity
            ]);

            $stores = Branch::select('name')->find($branch_id);


            $arr['id'] = (string) $stockRequest->id;
            $arr['store_id'] = (string) $branch_id;
            sendNotification('request_stock', $stores->name . ' store some Product is stock request', null, $branch_id, json_encode($arr));

            DB::commit();
            return redirect()->back()->with('success', 'Stock request submitted successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollback();
            return back()->with('error', 'Failed to submit stock request: ' . $e->getMessage());
        }
    }

    public function stockRequestFromStore(Request $request)
    {
        // $validated = $request->validate([
        //     'store_id' => 'required|exists:branches,id',
        //     'items' => 'required|array|min:1',
        //     'items.*.product_id' => 'required|exists:products,id',
        //     'items.*.quantity' => 'required|integer|min:1',
        //     'notes' => 'nullable|string',
        // ]);

        DB::beginTransaction();
        try {

            $data = User::with('userInfo')
                ->where('users.id', Auth::id())
                ->where('is_deleted', 'no')
                ->firstOrFail();

            $branch_id = $data->userInfo->branch_id;

            $stockRequest = StockRequest::create([
                'store_id' => 1,
                'requested_by' => $request['store_id'],
                'notes' => $request->notes,
                'requested_at' => now(),
                'created_by' => Auth::id(),
                // 'total_request_quantity' => $request->total_request_quantity,
            ]);

            foreach ($request['items'] as $item) {
                StockRequestItem::create([
                    'stock_request_id' => $stockRequest->id,
                    'request_to_location_id' => 1,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            $branch = Branch::where('id', $request['store_id'])->first();

            $arr['id'] = (string) $stockRequest->id;
            $arr['store_id'] = (string) $request['store_id'];

            sendNotification('request_stock', $branch->name . ' store is stock request', null, $branch_id, json_encode($arr));

            DB::commit();
            return redirect()->route('stock.requestList')->with('success', 'Stock request submitted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to submit stock request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockRequest $stockRequest)
    {
        $stockRequest->load('branch', 'user', 'items.product');
        return view('stocks.show', compact('stockRequest'));
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id');

        // Map frontend column names to actual DB columns
        switch ($orderColumn) {
            case 'name':
                $orderColumn = 'products.name';
                break;
            case 'created_at':
                $orderColumn = 'inventories.created_at';
                break;
            default:
                $orderColumn = 'inventories.created_at';
        }

        $orderDirection = $request->input('order.0.dir', 'desc');

        // Query with joins: products + inventories + branches
        $query = \App\Models\Inventory::select(
            'inventories.*',
            'products.name as product_name',
            'products.cost_price',
            'branches.name as branch_name'
        )
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->leftJoin('branches', 'inventories.store_id', '=', 'branches.id');

        // Search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('products.name', 'like', "%$searchValue%")
                    ->orWhere('products.cost_price', 'like', "%$searchValue%")
                    ->orWhere('inventories.batch_no', 'like', "%$searchValue%")
                    ->orWhere('branches.name', 'like', "%$searchValue%");
            });
        }

        if (in_array(session('role_name'), ['cashier', 'warehouse'])) {
            $data = User::with('userInfo')
                ->where('users.id', Auth::id())
                ->where('is_deleted', 'no')
                ->firstOrFail();

            $branch_id = $data->userInfo->branch_id;

            $query->where(function ($q) use ($branch_id) {
                $q->where('store_id', $branch_id);
            });
        }

        // if (in_array(session('role_name'), ['admin', 'owner', 'warehouse'])) {
        //     $query->where(function ($q) {
        //         $q->where('store_id', Auth::user()->branch_id)
        //           ->orWhere('requested_by', Auth::id());
        //     });
        // }

        $recordsTotal = \App\Models\Inventory::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];
        $url = url('/');

        foreach ($data as $inventory) {
            $status = ($inventory->quantity < $inventory->reorder_level)
                ? '<span class="badge bg-danger">Low Stock</span>'
                : '<span class="badge bg-success">OK</span>';

            $action = "";
            // $action .= "<a href='" . $url . "/inventory/edit/" . $inventory->id . "' class='btn btn-info mr-2'>Edit</a>";
            // $action .= '<button type="button" onclick="delete_inventory(' . $inventory->id . ')" class="btn btn-danger">Delete</button>';

            $records[] = [
                'name' => $inventory->product_name ?? 'N/A',
                'location' => $inventory->branch_name ?? '—',
                'quantity' => $inventory->quantity,
                'cost_price' => $inventory->cost_price,
                'batch_no' => $inventory->batch_no,
                'expiry_date' => $inventory->expiry_date,
                'reorder_level' => $inventory->reorder_level,
                'status' => $status,
                'created_at' => $inventory->updated_at ? $inventory->updated_at->format('d-m-Y h:i A') : '—',
                'action' => $action
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

    public function getRequestData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $orderColumn = match ($orderColumn) {
            'store' => 'stock_requests.id',
            'created_at' => 'stock_requests.updated_at',
            default => 'stock_requests.' . $orderColumn,
        };

        $query = StockRequest::select(
            'stock_requests.status',
            'stock_requests.id',
            'stock_requests.notes',
            'stock_requests.requested_at',
            'total_product',
            'total_quantity',
            'users.name as created_by_name',
            'users.email as created_by_email',
            'branches.name as branch_name'
        )
            ->join('users', 'stock_requests.created_by', '=', 'users.id')
            ->join('branches', 'stock_requests.requested_by', '=', 'branches.id');
        // ->where('stock_requests.created_by', Auth::id()); // static condition from the SQL

        // Optional search
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('stock_requests.notes', 'like', "%$searchValue%")
                    ->orWhere('branches.name', 'like', "%$searchValue%")
                    ->orWhere('users.name', 'like', "%$searchValue%");
            });
        }

        // Role-based filtering (keep your existing logic here)
        if (in_array(session('role_name'), ['cashier', 'warehouse'])) {
            $data = User::with('userInfo')
                ->where('users.id', Auth::id())
                ->where('is_deleted', 'no')
                ->firstOrFail();

            $branch_id = $data->userInfo->branch_id;

            $query->where(function ($q) use ($branch_id) {
                $q->where('stock_requests.store_id', $branch_id);
            });
        }

        $recordsTotal = StockRequest::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];
        $url = url('/');

        foreach ($data as $requestItem) {
            $action = "";
            $action = '<div class="d-flex align-items-center list-action">
                    <a class="badge badge-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="' . url('/stock/view/' . $requestItem->id) . '"><i class="ri-eye-line mr-0"></i></a>';

            if (in_array(session('role_name'), ['admin', 'warehouse'])) {
                if ($requestItem->status === 'pending') {
                    $action .= "<button class='btn btn-warning btn-sm ml-1 open-approve-modal' data-id='{$requestItem->id}'>Pending</button>";
                    // $action .=   '<a class="btn btn-warning btn-sm ml-1 mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    // href="' . url('/stock/stock-request-view/' . $requestItem->id) . '">Pending</a>';
                }
            }
            $action .= '</div>';

            $records[] = [
                'id' => $requestItem->id,
                'store' => $requestItem->branch_name,
                'requested_at' => optional($requestItem->requested_at)->format('d-m-Y H:i'),
                'total_quantity' => $requestItem->total_quantity,
                'total_product' => $requestItem->total_product,
                'status' => ucfirst($requestItem->status),
                'action' => $action
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

    public function showSendRequest(StockRequest $stockRequest)
    {
        // $stockRequest->load('store', 'items.product');
        $stockRequest->load('branch', 'user', 'items.product');
        return view('stocks.showSendRequest', compact('stockRequest'));
    }

    public function getSendRequestData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $orderColumn = match ($orderColumn) {
            'store' => 'stock_requests.id',
            'created_at' => 'stock_requests.updated_at',
            default => 'stock_requests.' . $orderColumn,
        };

        $query = StockRequest::select(
            'stock_requests.status',
            'stock_requests.notes',
            'users.name as created_by_name',
            'users.email as created_by_email',
            'branches.name as branch_name'
        )
            ->join('users', 'stock_requests.created_by', '=', 'users.id')
            ->join('branches', 'stock_requests.requested_by', '=', 'branches.id')
            ->where('stock_requests.created_by', 3); // static condition from the SQL

        // Optional search
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('stock_requests.notes', 'like', "%$searchValue%")
                    ->orWhere('branches.name', 'like', "%$searchValue%")
                    ->orWhere('users.name', 'like', "%$searchValue%");
            });
        }

        // Role-based filtering (keep your existing logic here)
        if (in_array(session('role_name'), ['cashier', 'warehouse'])) {
            $data = User::with('userInfo')
                ->where('users.id', Auth::id())
                ->where('is_deleted', 'no')
                ->firstOrFail();

            $branch_id = $data->userInfo->branch_id;

            $query->where(function ($q) use ($branch_id) {
                $q->where('stock_requests.created_by', Auth::id());
            });
        }

        $recordsTotal = StockRequest::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();



        $records = [];
        $url = url('/');

        foreach ($data as $requestItem) {
            $action = "";
            $action .= "<a href='" . $url . "/stock/view/" . $requestItem->id . "' class='btn btn-sm btn-primary'>View</a> ";
            // $action .= "<form method='POST' action='" . $url . "/stock/view/" . $requestItem->id . "' style='display:inline;'>"
            //          . csrf_field() . method_field('DELETE')
            //          . "<button class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</button></form>";

            //          if ($requestItem->status === 'pending') {
            //             $action .= "<button class='btn btn-success btn-sm ml-1 open-approve-modal' data-id='{$requestItem->id}'>Approve</button>";

            //             // $action .= "<button class='btn btn-success btn-sm approve-btn ml-1' data-id='{$requestItem->id}'>Approve</button>";
            //         }
            $records[] = [
                'id' => $requestItem->id,
                'store' => $requestItem->branch_name,
                'requested_by' => $requestItem->created_by_name,
                'requested_at' => optional($requestItem->requested_at)->format('d-m-Y H:i'),
                'status' => ucfirst($requestItem->status),
                'action' => $action
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array',
            // 'items.*' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $stockRequest = StockRequest::with('items')->findOrFail($id);
            $from_store_id = $request->from_store_id;
            $transferNumber = 'TRF-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

            if ($stockRequest->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Request already processed.']);
            }

            $running_shift = ShiftClosing::where('branch_id', $request->to_store_id)
                ->where('status', 'pending')
                ->first();

            // if (!$running_shift) {            // null  ➔ destination store not 
            //     return response()->json(['status' => 'error', 'message' => 'The destination store is not open.']);
            // }


            if ($from_store_id == 1) {
                $arr_low_stock = [];

                foreach ($request->items as $key => $val) {
                    $to_store_id = $key;
                    foreach ($val as $product_id => $product_qun) {
                        // Decrease from warehouse
                        if (!empty($product_qun)) {

                            $inventories = Inventory::where('product_id', $product_id)->where('store_id', $to_store_id)->orderBy('expiry_date')->get(); // optional: FIFO

                            $totalQuantity = $inventories->sum('quantity');

                            if ($totalQuantity < $product_qun) {
                                return response()->json([
                                    'status' => 'error',
                                    'message' => "Not enough stock for product"
                                ]);
                            }

                            $remainingQty = $product_qun;

                            foreach ($inventories as $inventory) {
                                if ($remainingQty <= 0) break;

                                $deducted = min($inventory->quantity, $remainingQty);

                                // Deduct from warehouse
                                $inventory->quantity -= $deducted;
                                $inventory->save();

                                $low_qty_level = Inventory::lowLevelQty($product_id, 1);

                                $total_qty = Inventory::countQty($product_id, 1);
                                $total_qty = $total_qty + $deducted;

                                if ($total_qty < $low_qty_level) {
                                    // $arr['id'] = (string) $product_id;
                                    $arr_llp = (string) $product_id;
                                }

                                // Add to store inventory
                                $storeInventory = Inventory::firstOrNew([
                                    'store_id' => $stockRequest->requested_by,
                                    'location_id' => $from_store_id,
                                    'product_id' => $product_id,
                                    'batch_no' => $inventory->batch_no,
                                    'expiry_date' => $inventory->expiry_date,
                                    // 'reorder_level' => $inventory->reorder_level,
                                    // 'cost_price' => $inventory->cost_price,
                                    // 'sell_price' => $inventory->sell_price,
                                ]);

                                $storeInventory->quantity += $deducted;
                                $storeInventory->save();

                                // $low_qty_level = Inventory::lowLevelQty($product_id, $from_store_id);

                                // $total_qty = Inventory::countQty($product_id, $from_store_id);
                                // $total_qty = $total_qty - $deducted;

                                // if ($total_qty < $low_qty_level) {
                                //     $arr_low_stock[$product_id] = $product_id;
                                // }

                                // Transfer log
                                $inventoryService = new \App\Services\InventoryService();
                                $data = User::with('userInfo')
                                    ->where('users.id', Auth::id())
                                    ->where('is_deleted', 'no')
                                    ->firstOrFail();

                                $inventoryService->transferProduct(
                                    $product_id,
                                    $inventory->id,
                                    $from_store_id,
                                    $stockRequest->requested_by,
                                    $deducted,
                                    'warehouse_to_store',
                                    'store'
                                );

                                $remainingQty -= $deducted;

                                stockStatusChange($product_id, $key, $product_qun, 'transfer_stock');
                                stockStatusChange($product_id, $from_store_id, $product_qun, 'add_stock');

                                StockTransfer::create([
                                    'stock_request_id' => $request->request_id,
                                    'transfer_number' => $transferNumber,
                                    'from_branch_id' => $from_store_id,
                                    'to_branch_id' => $key,
                                    'product_id' => $product_id,
                                    'quantity' => $product_qun,
                                    'status' => 'approved', // or 'completed' depending on flow
                                    'transfer_by' => Auth::id(),
                                    'transferred_at' => now(),
                                ]);
                            }
                        } else {
                            $stockItem = StockRequestItem::where('product_id', $product_id)->where('stock_request_id', $id)->firstOrFail(); // optional: FIFO
                            $stockItem->quantity = 0;
                            $stockItem->save();
                        }
                    }
                }

                // if (!empty($arr_low_stock)) {

                //     $arr['product_id'] =  implode(',', array_values($arr_low_stock));
                //     $arr['store_id'] =  (string) $request->from_store_id;

                //     // sendNotification('low_stock', 'Some products are running low', $request->from_store_id, Auth::id(), json_encode($arr));
                //     sendNotification('low_stock', 'Some products are running low', null, Auth::id(), json_encode($arr));
                // }
            } else {

                $arr_low_stock = [];

                foreach ($request->items as $key => $val) {
                    foreach ($val as $product_id => $product_qun) {
                        // Decrease from warehouse

                        if (!empty($product_qun)) {

                            $inventories = Inventory::where('product_id', $product_id)->orderBy('expiry_date')->get(); // optional: FIFO

                            $totalQuantity = $inventories->sum('quantity');

                            if ($totalQuantity < $product_qun) {
                                return response()->json([
                                    'status' => 'error',
                                    'message' => "Not enough stock for product"
                                ]);
                            }

                            $remainingQty = $product_qun;


                            foreach ($inventories as $inventory) {
                                if ($remainingQty <= 0) break;

                                $deducted = min($inventory->quantity, $remainingQty);

                                // Deduct from warehouse
                                $inventory->quantity -= $deducted;
                                $inventory->save();

                                // Add to store inventory
                                $storeInventory = Inventory::firstOrNew([
                                    'store_id' => $stockRequest->requested_by,
                                    'location_id' => $from_store_id,
                                    'product_id' => $product_id,
                                    'batch_no' => $inventory->batch_no,
                                    'expiry_date' => $inventory->expiry_date,
                                    // 'reorder_level' => $inventory->reorder_level,
                                    // 'cost_price' => $inventory->cost_price,
                                    // 'sell_price' => $inventory->sell_price,
                                ]);

                                $storeInventory->quantity += $deducted;
                                $storeInventory->save();

                                // Transfer log
                                $inventoryService = new \App\Services\InventoryService();
                                $data = User::with('userInfo')
                                    ->where('users.id', Auth::id())
                                    ->where('is_deleted', 'no')
                                    ->firstOrFail();

                                $inventoryService->transferProduct(
                                    $product_id,
                                    $inventory->id,
                                    $from_store_id,
                                    $stockRequest->requested_by,
                                    $deducted,
                                    'warehouse_to_store',
                                    'store'
                                );

                                $remainingQty -= $deducted;

                                // $low_qty_level = Inventory::lowLevelQty($product_id, $from_store_id);

                                // $total_qty = Inventory::countQty($product_id, $from_store_id);
                                // $total_qty = $total_qty - $deducted;

                                // if ($total_qty < $low_qty_level) {
                                //     $arr_low_stock[$product_id] = $product_id;
                                // }

                                stockStatusChange($product_id, $key, $product_qun, 'transfer_stock');
                                stockStatusChange($product_id, $from_store_id, $product_qun, 'add_stock');

                                StockTransfer::create([
                                    'stock_request_id' => $request->request_id,
                                    'transfer_number' => $transferNumber,
                                    'from_branch_id' => $from_store_id,
                                    'to_branch_id' => $key,
                                    'product_id' => $product_id,
                                    'quantity' => $product_qun,
                                    'status' => 'approved', // or 'completed' depending on flow
                                    'transfer_by' => Auth::id(),
                                    'transferred_at' => now(),
                                ]);
                            }

                            // if (!empty($arr_low_stock)) {

                            //     $arr['product_id'] =  implode(',', array_values($arr_low_stock));
                            //     $arr['store_id'] =  (string) $request->from_store_id;

                            //     sendNotification('low_stock', 'Some products are running low', $request->from_store_id, Auth::id(), json_encode($arr));
                            //     // sendNotification('low_stock', 'Some products are running low', null, Auth::id(), json_encode($arr));
                            // }
                        } else {

                            $stockItem = StockRequestItem::where('product_id', $product_id)->where('stock_request_id', $id)->firstOrFail(); // optional: FIFO
                            $stockItem->quantity = 0;
                            $stockItem->save();
                        }
                    }
                }
            }

            // if (!emptyArray($arr_llp)) {
            // $ids =implode(',',$arr_llp);
            // $arr['product_id'] = (string) $ids;
            // sendNotification('low_stock', 'Store stock request', null, Auth::id(), json_encode($arr));
            // }
            $stockRequest->status = 'approved';
            $stockRequest->approved_by = Auth::id();
            $stockRequest->approved_at = now();
            $stockRequest->save();

            $arr['id'] = (string) $stockRequest->id;
            // $arr['store_id'] = $branch_id;
            sendNotification('approved_stock', 'Admin your stock request has been approved', $request->from_store_id, Auth::id(), json_encode($arr));

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Approved successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function view($id)
    {
        $stockRequest = StockRequest::with(['branch', 'tobranch', 'user', 'items.product'])->findOrFail($id);
        return view('stocks.view', compact('stockRequest'));
    }

    public function stockRequestView($id)
    {

        $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
        $arr_val = [];
        $storeWiseData = [];

        foreach ($stockRequest->items as $item) {
            $storeId = $item->request_to_location_id;

            // Get inventory quantity
            $inventory = Inventory::select('quantity')
                ->where('product_id', $item->product_id)
                ->where('store_id', $storeId)
                ->first();

            $storeWiseData[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'store_id' => $storeId,
                'product_name' => $item->product->name ?? 'N/A',
                'req_quantity' => $item->quantity,
                'store_ava_quantity' => $inventory->quantity ?? 0,
            ];
        }

        $data['items'] = $storeWiseData;

        return view('stocks.stockRequestView', compact('stockRequest', 'data'));
    }

    public function getStockRequestDetails(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'name');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $stockRequestId = $request->input('stock_request_id');

        // Fetch stock request with products
        $stockRequest = StockRequest::with(['items.product'])->findOrFail($stockRequestId);

        // Prepare product list
        $allProducts = collect($stockRequest->items)->map(function ($item) {
            return [
                'name' => $item->product->name ?? '',
                'size' => $item->product->size ?? '',
                'quantity' => $item->quantity ?? 0,
            ];
        });

        // Filter by search term (case-insensitive)
        if (!empty($searchValue)) {
            $searchValue = strtolower($searchValue);
            $allProducts = $allProducts->filter(function ($product) use ($searchValue) {
                return str_contains(strtolower($product['name']), $searchValue) ||
                    str_contains(strtolower($product['size']), $searchValue);
            });
        }

        $recordsFiltered = $allProducts->count();
        $recordsTotal = count($stockRequest->items);

        // Sort by column
        $allProducts = $allProducts->sortBy([
            [$orderColumn, $orderDirection === 'asc' ? SORT_ASC : SORT_DESC],
        ]);

        // Paginate
        $paginated = $allProducts->slice($start, $length)->values();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $paginated,
        ]);
    }

    public function approve_backup(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $stockRequest = StockRequest::with('items')->findOrFail($id);

            if ($stockRequest->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Request already processed.']);
            }

            foreach ($stockRequest->items as $item) {
                $updatedQty = $request->items[$item->id];

                // Update quantity in StockRequestItem
                $item->quantity = $updatedQty;
                $item->save();

                // Decrease from warehouse
                $inventories = Inventory::where('product_id', $item->product_id)->orderBy('expiry_date')->get(); // optional: FIFO

                $totalQuantity = $inventories->sum('quantity');

                if ($totalQuantity < $updatedQty) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Not enough stock for product {$item->product->name}"
                    ]);
                }

                $remainingQty = $updatedQty;

                foreach ($inventories as $inventory) {
                    if ($remainingQty <= 0) break;

                    $deducted = min($inventory->quantity, $remainingQty);

                    // Deduct from warehouse
                    $inventory->quantity -= $deducted;
                    $inventory->save();

                    // Add to store inventory
                    $storeInventory = Inventory::firstOrNew([
                        'store_id' => $stockRequest->requested_by,
                        'location_id' => Auth::id(),
                        'product_id' => $item->product_id,
                        'batch_no' => $inventory->batch_no,
                        'expiry_date' => $inventory->expiry_date,
                        'reorder_level' => $inventory->reorder_level,
                        'cost_price' => $inventory->cost_price,
                        'sell_price' => $inventory->sell_price,
                    ]);

                    $storeInventory->quantity += $deducted;
                    $storeInventory->save();

                    // Transfer log
                    $inventoryService = new \App\Services\InventoryService();
                    $data = User::with('userInfo')
                        ->where('users.id', Auth::id())
                        ->where('is_deleted', 'no')
                        ->firstOrFail();
                    $branch_id = $data->userInfo->branch_id;

                    $inventoryService->transferProduct(
                        $item->product_id,
                        $inventory->id,
                        $branch_id,
                        $stockRequest->requested_by,
                        $deducted,
                        'warehouse_to_store',
                        'store'
                    );

                    $remainingQty -= $deducted;
                }
            }

            $stockRequest->status = 'approved';
            $stockRequest->approved_by = Auth::id();
            $stockRequest->approved_at = now();
            $stockRequest->save();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Approved successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function stockShow($id)
    {

        $stockRequest = StockRequest::with(['branch', 'items.product'])->findOrFail($id);

        $arr_val = [];
        $storeWiseData = [];

        foreach ($stockRequest->items as $item) {
            $storeId = $item->request_to_location_id;

            // Initialize group if not already
            if (!isset($storeWiseData[$storeId])) {
                $store = Branch::select('name')->find($storeId);

                $storeWiseData[$storeId] = [
                    'store_id' => $storeId,
                    'store_name' => $store?->name ?? 'N/A',
                    'items' => [],
                ];
            }

            // Get inventory quantity
            $inventory = Inventory::select('quantity')
                ->where('product_id', $item->product_id)
                ->where('store_id', $storeId)
                ->first();

            $storeWiseData[$storeId]['items'][] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'store_id' => $storeId,
                'product_name' => $item->product->name ?? 'N/A',
                'req_quantity' => $item->quantity,
                'store_ava_quantity' => $inventory->quantity ?? 0,
            ];
        }

        $data['stockRequest']['store_id'] = $stockRequest->branch->id;
        $data['stockRequest']['branch_name'] = $stockRequest->branch->name;
        $data['items_by_store'] = $storeWiseData;

        return response()->json($data);
    }

    public function destroy(StockRequest $stockRequest)
    {
        $stockRequest->delete();
        return redirect()->route('stock-requests.index')->with('success', 'Stock request deleted.');
    }

    public function storeWarehouse_ori(Request $request)
    {

        // $validated = $request->validate([
        //     'items' => 'required|array|min:1',
        //     'items.*.product_id' => 'required|exists:products,id',
        //     'items.*.quantity' => 'required|numeric|min:1',
        //     'items.*.branches' => 'required|array|min:1',
        //     'items.*.branch_quantities' => 'required|array',
        //     'notes' => 'nullable|string',
        // ]);

        $data = User::with('userInfo')
            ->where('users.id', Auth::id())
            ->where('is_deleted', 'no')
            ->firstOrFail();

        $store_id = $request->store_id;

        $branch_id = $data->userInfo->branch_id;

        $stockRequest = StockRequest::create([
            'store_id' => 1,
            'requested_by' => $store_id,
            'notes' => $request->notes,
            'requested_at' => now(),
            'created_by' => Auth::id(),
        ]);

        $totalProductCount = 0;
        $totalQuantitySum = 0;
        $uniqueProductIds = [];

        foreach ($validated['items'] as $item) {
            $productId = $item['product_id'];
            $totalQty = (int) $item['quantity'];
            $branches = $item['branches'] ?? [];
            $quantities = $item['branch_quantities'] ?? [];

            // Filter out unchecked branches
            $branchQuantities = [];
            foreach ($branches as $branch => $checked) {
                if (isset($quantities[$branch])) {
                    $branchQuantities[$branch] = (int) $quantities[$branch];
                }
            }

            // Optional: validate that branch total matches or doesn't exceed total
            $sum = array_sum($branchQuantities);
            if ($sum > $totalQty) {
                return back()->withErrors(['items' => "Total quantity for product ID $productId is less than sum of branch quantities."])->withInput();
            }

            // Track unique product and sum quantity
            if ($sum > 0) {
                if (!in_array($productId, $uniqueProductIds)) {
                    $uniqueProductIds[] = $productId;
                }
                $totalQuantitySum += $sum;
            }

            foreach ($branches as $branch => $checked) {

                $quantity = $item['branch_quantities'][$branch] ?? null;

                StockRequestItem::create([
                    'request_to_location_id' => $branch,
                    'stock_request_id' => $stockRequest->id,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
            }
        }

        // 🔄 Update totals
        $stockRequest->update([
            'total_product' => count($uniqueProductIds),
            'total_quantity' => $totalQuantitySum
        ]);

        return redirect()->route('stock.requestList')->with('success', 'Stock request submitted successfully.');
    }
}
