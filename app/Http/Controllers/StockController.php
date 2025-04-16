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

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data =Inventory::with('product')->get();
        return view('stocks.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function add()
    {
        $stores = Branch::all();
        $products = Product::all();
        $data = User::with('userInfo')
        ->where('users.id', Auth::id())
        ->where('is_deleted', 'no')
        ->firstOrFail();
    
        return view('stocks.create', compact('stores', 'products','data'));
    }

    public function addWarehouse()
    {
        $stores = Branch::all();
        $products = Product::all();
    
        return view('stocks.create_warehouse', compact('stores', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeWarehouse(Request $request)
{
    $validated = $request->validate([
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|numeric|min:1',
        'items.*.branches' => 'required|array|min:1',
        'items.*.branch_quantities' => 'required|array',
        'notes' => 'nullable|string',
    ]);

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

        $data = User::with('userInfo')
        ->where('users.id', Auth::id())
        ->where('is_deleted', 'no')
        ->firstOrFail();

$branch_id = $data->userInfo->branch_id;

        foreach ($branches as $branch => $checked) {

            $quantity = $item['branch_quantities'][$branch] ?? null;
       
            $stockRequest = StockRequest::create([
                'store_id' => $branch,
                'requested_by' => $branch_id,
                'notes' => $request->notes,
                'requested_at' => now(),
                'created_by' => Auth::id(),
            ]);
    
            StockRequestItem::create([
                'stock_request_id' => $stockRequest->id,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }
        
    }

    return redirect()->route('stock.requestList')->with('success', 'Stock request submitted successfully.');
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
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

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
        // $stockRequest->load('store', 'items.product');
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
                'inventories.cost_price',
                'branches.name as branch_name'
            )
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->leftJoin('branches', 'inventories.location_id', '=', 'branches.id');
    
        // Search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('products.name', 'like', "%$searchValue%")
                  ->orWhere('inventories.cost_price', 'like', "%$searchValue%")
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

    public function view($id)
    {
        $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
        // dd($stockRequest);
    return view('stocks.view', compact('stockRequest'));
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
            'stock_requests.status','stock_requests.id',
            'stock_requests.notes',
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

        // dd($data);

    $records = [];
    $url = url('/');

    foreach ($data as $requestItem) {
        $action = "";
        $action .= "<a href='" . $url . "/stock/view/" . $requestItem->id . "' class='btn btn-sm btn-primary'>View</a> ";
        $action .= "<form method='POST' action='" . $url . "/stock/view/" . $requestItem->id . "' style='display:inline;'>"
                 . csrf_field() . method_field('DELETE')
                 . "<button class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</button></form>";
        

        
    if (in_array(session('role_name'), ['admin', 'warehouse'])) {
        if ($requestItem->status === 'pending') {
            $action .= "<button class='btn btn-success btn-sm ml-1 open-approve-modal' data-id='{$requestItem->id}'>Approve</button>";
            // $action .= "<button class='btn btn-success btn-sm approve-btn ml-1' data-id='{$requestItem->id}'>Approve</button>";
        }
    }
                 
        $records[] = [
            'id' => $requestItem->id,
            'store' => $requestItem->store->name ?? 'warehouse',
            'requested_by' => $requestItem->user->name ?? 'N/A',
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
                $warehouse = Inventory::where('store_id',1)->where('product_id', $item->product_id)->first();
                if (!$warehouse || $warehouse->quantity < $updatedQty) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Not enough stock for product {$item->product->name}"
                    ]);
                }
                $warehouse->quantity -= $updatedQty;

                // dd($warehouse->quantity);
                $warehouse->save();

                // Increase to store
                $storeInventory = Inventory::firstOrNew([
                    'store_id' => $stockRequest->requested_by,
                    'location_id'=> Auth::id(),
                    'product_id' => $item->product_id,
                    'batch_no' => $warehouse->batch_no,
                    'expiry_date' => $warehouse->expiry_date,
                    'reorder_level' => $warehouse->reorder_level,
                    'cost_price' => $warehouse->cost_price,
                    'sell_price' => $warehouse->sell_price,
                ]);
                $storeInventory->quantity += $updatedQty;
                $storeInventory->save();
                // dd($storeInventory->save());
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

        $stockRequest = StockRequest::with(['branch', 'user', 'items.product'])->findOrFail($id);
            // dd($stockRequest);
    
        // $stockRequest->load('items.product');

        return response()->json($stockRequest);
    }

    public function destroy(StockRequest $stockRequest)
    {
        $stockRequest->delete();
        return redirect()->route('stock-requests.index')->with('success', 'Stock request deleted.');
    }
}
