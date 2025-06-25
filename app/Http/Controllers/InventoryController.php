<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\UserInfo;
use App\Models\VendorList;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Branch;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    // 🧾 GET /api/inventory
    public function index()
    {
        $data = Inventory::with('product')->get();
        $branch = Branch::where('is_deleted', 'no')->pluck('name', 'id');
        return view('inventories.index', compact('data', 'branch'));
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
                $orderColumn = 'inventories.updated_at';
        }

        $orderDirection = $request->input('order.0.dir', 'desc');

        // Query with joins: products + inventories + branches
        $query = \App\Models\Inventory::select(
            'inventories.*',
            'products.name as product_name',
            'products.reorder_level',
            'products.cost_price',
            'branches.name as branch_name'
        )
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->leftJoin('branches', 'inventories.store_id', '=', 'branches.id');

        $query->where('products.is_deleted', 'no');
        // Search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('products.name', 'like', "%$searchValue%")
                    ->orWhere('products.cost_price', 'like', "%$searchValue%")
                    ->orWhere('products.barcode', 'like', "%$searchValue%")
                    ->orWhere('inventories.batch_no', 'like', "%$searchValue%")
                    ->orWhere('branches.name', 'like', "%$searchValue%");
            });
        }


        if ($request->has('store_id') && $request->store_id != '') {
            $query->where('branches.id', $request->store_id);
        }

        if (in_array(session('role_name'), ['warehouse'])) {

            $query->where(function ($q) {
                // $q->where('inventories.vendor_id', "!=", '');
                $q->where('branches.id', 1);
            });
        }

        $recordsTotal = \App\Models\Inventory::count();
        $recordsFiltered = $query->count();

        // $data = $query->orderBy($orderColumn, $orderDirection)
        //     ->offset($start)
        //     ->limit($length)
        //     ->get();
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $data = $query->orderBy($orderColumn, $orderDirection)->get();


        $records = [];
        $url = url('/');

        foreach ($data as $inventory) {
            $total_qty = Inventory::countQty($inventory->product_id, $inventory->store_id);

            $reorder_level = ($total_qty < $inventory->low_level_qty)
                ? '<span class="badge bg-danger" onclick="low_level_stock_set(' . $inventory->product_id . ',' . $inventory->store_id . ',' . $inventory->low_level_qty . ')">Low Level (' . $inventory->low_level_qty . ')</span>'
                : '<span class="badge bg-success" onclick="low_level_stock_set(' . $inventory->product_id . ',' . $inventory->store_id . ',' . $inventory->low_level_qty . ')">' . $inventory->low_level_qty . '</span>';
            $status = ($inventory->status == 'Yes')
                ? '<span class="badge bg-danger">Active</span>'
                : '<span class="badge bg-success">Inactive</span>';

            $action = "";
            if (session('role_name') == "admin") {

                // $action ='<div class="d-flex align-items-center list-action">
                // <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"
                //     href="' . url('/inventories/edit/' . $inventory->id) . '"><i class="ri-pencil-line mr-0"></i></a>
                // </div>';
            }

            $records[] = [
                'name' => $inventory->product_name ?? 'N/A',
                'location' => $inventory->branch_name ?? '—',
                'quantity' => $inventory->quantity,
                'cost_price' => $inventory->cost_price,
                'batch_no' => $inventory->batch_no,
                'expiry_date' => $inventory->expiry_date ? $inventory->expiry_date->format('d-m-Y') : '—',
                'reorder_level' => $reorder_level,
                'status' => $status,
                'updated_at' => $inventory->updated_at ? $inventory->updated_at->format('d-m-Y h:i A') : '—',
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

    // 🧾 POST /api/inventory
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|integer',
            'location_type' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'low_stock_alert_level' => 'nullable|integer|min:0',
        ]);

        $inventory = Inventory::create($data);
        return response()->json($inventory, 201);
    }

    // 🧾 GET /api/inventory/{id}
    public function addStock($id)
    {
        $product_details = Product::with(['category', 'subcategory'])
            ->where('id', $id)
            ->where('is_deleted', 'no')
            ->firstOrFail();

        $vendors = VendorList::where('is_active', true)->get();
        return view('inventories.add_stock', compact('product_details', 'vendors'));
    }

    public function edit($id)
    {

        $inventory = Inventory::with(['product.category', 'product.subcategory'])
            ->where('id', $id) // $id is inventories.id
            ->firstOrFail();

        $product_details = Product::with(['inventories', 'category', 'subcategory'])
            ->where('id', $inventory->product_id)
            ->where('is_deleted', 'no')
            ->firstOrFail();

        // Get all vendors
        $vendors = VendorList::where('is_active', true)->get();
        return view('inventories.edit', compact('product_details', 'vendors', 'inventory'));
    }

    public function editStock($id)
    {
        $product_details = Product::with(['inventories', 'category', 'subcategory'])
            ->where('id', $id)
            ->where('is_deleted', 'no')
            ->firstOrFail();

        $inventory = Inventory::with(['product.category', 'product.subcategory'])
            ->where('id', $id) // $id is inventories.id
            ->firstOrFail();
        // dd($inventory);
        // Get all vendors
        $vendors = VendorList::where('is_active', true)->get();
        return view('inventories.edit_stock', compact('product_details', 'vendors', 'inventory'));
    }

    public function storeStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'expiry_date' => 'required|date|after:today',
            'quantity' => 'required|integer|min:1',
            'vendor_id' => 'nullable|exists:vendor_lists,id',
            'mfg_date' => 'nullable|date',
        ]);

        $user_id = Auth::id();

        $user_details = UserInfo::select('branch_id')
            ->where('user_id', $user_id)
            ->firstOrFail();

        $batchNumber = strtoupper($request->sku) . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));

        $inventory = Inventory::firstOrCreate([
            'product_id'  => $validated['product_id'],
            'store_id'    => 1,
            'location_id'    => 1,
            'batch_no'    => $batchNumber,
            'expiry_date' => $validated['expiry_date'],
            'added_by' => $user_id,
        ]);

        // Add stock
        $inventory->quantity += $validated['quantity'];

        // Optionally update pricing
        if (isset($validated['cost_price'])) {
            $inventory->cost_price = $validated['cost_price'];
        }
        if (isset($validated['sell_price'])) {
            $inventory->sell_price = $validated['sell_price'];
        }

        $inventory->save();
        $inventoryService = new \App\Services\InventoryService();

        $inventoryService->transferProduct($validated['product_id'], $inventory->id, $user_details->branch_id, '', $validated['quantity'], 'add_stock');
        return redirect()->route('inventories.list')->with('success', 'Stock added successfully!.');
    }

    public function show($id)
    {
        $inventory = Inventory::with('product')->findOrFail($id);
        return response()->json($inventory);
    }

    // 🧾 PUT /api/inventory/{id}
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $data = $request->validate([
            'quantity' => 'sometimes|integer|min:0',
            'low_stock_alert_level' => 'nullable|integer|min:0',
        ]);

        $inventory->update($data);
        return response()->json($inventory);
    }

    // 🧾 DELETE /api/inventory/{id}
    public function destroy($id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    // 🔔 GET /api/inventory/low-stock


    public function stockList()
    {
        $data = Inventory::with('product')->get();
        return view('inventories.index', compact('data'));
    }

    public function getStockData(Request $request)
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

    public function lowStock()
    {
        return $this->inventoryService->getLowStockItems();
    }

    public function countQty($p_id, $store_id)
    {
        return Inventory::where('product_id', $p_id)
            ->where('store_id', $store_id)
            ->sum('qty'); // assuming your column is named 'qty'
    }

    public function updateLowLevelQty(Request $request)
    {
        $request->validate([
            'store_id' => 'required|integer',
            'product_id' => 'required|integer',
            'low_level_qty' => 'required|integer|min:0',
        ]);

        $storeId = $request->store_id;
        $productId = $request->product_id;
        $lowLevelQty = $request->low_level_qty;

        if ($storeId == 1) {
            // Update reorder_level in products table
            $product = Product::find($productId);
            if ($product) {
                $product->reorder_level = $lowLevelQty;
                $product->save();
            }
            Inventory::where('product_id', $productId)
                ->where('store_id', $storeId)
                ->update(['low_level_qty' => $lowLevelQty]);
        } else {
            // Update low_level_qty in inventories table for matching product_id AND store_id
            Inventory::where('product_id', $productId)
                ->where('store_id', $storeId)
                ->update(['low_level_qty' => $lowLevelQty]);
        }

        return response()->json(['message' => 'Low level quantity updated successfully.']);
    }

    public function getLowLevelProducts($storeId)
    {
        $products = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.store_id', $storeId)
            ->where('products.is_deleted', 'no')
            ->where('products.is_active', 'yes')
            ->select('products.name', 'inventories.low_level_qty', 'inventories.product_id as id')
            ->groupBy('inventories.product_id', 'products.name', 'inventories.low_level_qty')
            ->get();

        return response()->json($products);
    }

    public function updateMultipleLowLevelQty(Request $request)
    {
        $storeId = $request->input('store_id');
        $products = $request->input('low_level_qty');

        // dd($products);

        foreach ($products as $key => $product) {
            // $productId = $product['product_id'];
            // $lowLevelQty = $product['low_level_qty'];

            
            $productId = $key;
            $lowLevelQty = $product;

            if ($storeId == 1) {
                // Update reorder_level in product table
                \App\Models\Product::where('id', $productId)->update([
                    'reorder_level' => $lowLevelQty
                ]);
            }

            // Update all inventories for that product & store
            \App\Models\Inventory::where('product_id', $productId)
                ->where('store_id', $storeId)
                ->update(['low_level_qty' => $lowLevelQty]);
        }

        return response()->json(['success' => true]);
    }
}
