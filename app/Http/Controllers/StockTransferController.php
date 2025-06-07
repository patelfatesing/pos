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

class StockTransferController extends Controller
{
    public function index()
    {
        return view('stocks_transfer.list');
    }

    public function craeteTransfer()
    {
        $stores = Branch::where('is_deleted', 'no')->get();
        $products = Product::all();
        $data = User::with('userInfo')
            ->where('users.id', Auth::id())
            ->where('is_deleted', 'no')
            ->firstOrFail();

        return view('stocks_transfer.create', compact('stores', 'products', 'data'));
    }

    public function getData(Request $request)
    {
        $stockTransfer = new StockTransfer();
        $transfers = $stockTransfer->getTransferData();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $transfers->count(),
            'recordsFiltered' => $transfers->count(),
            'data' => $transfers
        ]);
    }

    public function getTransferData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id');
        $orderDirection = $request->input('order.0.dir', 'asc');

        $query = StockTransfer::select([
            'stock_transfers.transfer_number',
            'stock_transfers.from_branch_id',
            'stock_transfers.to_branch_id',
            'stock_transfers.status',
            'stock_transfers.transfer_by',
            'stock_transfers.transferred_at',
            'from_branch.name as from_branch_name',
            'to_branch.name as to_branch_name',
            'users.name as created_by_name',
            DB::raw('COUNT(DISTINCT stock_transfers.product_id) as total_products'),
            DB::raw('SUM(stock_transfers.quantity) as total_quantity')
        ])
            ->join('branches as from_branch', 'stock_transfers.from_branch_id', '=', 'from_branch.id')
            ->join('branches as to_branch', 'stock_transfers.to_branch_id', '=', 'to_branch.id')
            ->join('users', 'stock_transfers.transfer_by', '=', 'users.id')
            ->groupBy([
                'stock_transfers.transfer_number',
                'stock_transfers.from_branch_id',
                'stock_transfers.to_branch_id',
                'stock_transfers.status',
                'stock_transfers.transfer_by',
                'stock_transfers.transferred_at',
                'from_branch.name',
                'to_branch.name',
                'users.name'
            ]);

        // Optional search
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('stock_transfers.transfer_number', 'like', "%$searchValue%")
                    ->orWhere('from_branch.name', 'like', "%$searchValue%")
                    ->orWhere('to_branch.name', 'like', "%$searchValue%")
                    ->orWhere('users.name', 'like', "%$searchValue%");
            });
        }

        // Role-based filtering
        if (in_array(session('role_name'), ['cashier', 'warehouse'])) {
            $data = User::with('userInfo')
                ->where('users.id', Auth::id())
                ->where('is_deleted', 'no')
                ->firstOrFail();

            $branch_id = $data->userInfo->branch_id;

            $query->where(function ($q) use ($branch_id) {
                $q->where('stock_transfers.from_branch_id', $branch_id)
                    ->orWhere('stock_transfers.to_branch_id', $branch_id);
            });
        }

        $recordsTotal = StockTransfer::select('transfer_number')->distinct()->count();
        $recordsFiltered = $query->get()->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        foreach ($data as $transfer) {
            $records[] = [
                'id' => $transfer->transfer_number,
                'transfer_number' => $transfer->transfer_number,
                'from' => $transfer->from_branch_name,
                'to' => $transfer->to_branch_name,
                'transferred_at' => $transfer->transferred_at ? date('d-m-Y H:i', strtotime($transfer->transferred_at)) : 'N/A',
                'status' => ucfirst($transfer->status),
                'created_by' => $transfer->created_by_name,
                'total_products' => $transfer->total_products,
                'total_quantity' => $transfer->total_quantity,
                'action' => '<div class="d-flex align-items-center list-action">
                    <a class="badge badge-info mr-2" data-toggle="tooltip" data-placement="top" title="View"
                        href="' . route('stock-transfer.view', $transfer->transfer_number) . '"><i class="ri-eye-line mr-0"></i></a>
                </div>'
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

    public function view($transferNumber)
    {
        $stockTransfer = new StockTransfer();
        $transfer = $stockTransfer->getTransferDetails($transferNumber);

        if (!$transfer) {
            abort(404);
        }

        return view('stocks_transfer.view', [
            'stockTransfer' => $transfer,
            'transferProducts' => $transfer->products
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validate basic form inputs
            $validated = $request->validate([
                'from_store_id' => 'required|exists:branches,id',
                'to_store_id' => 'required|exists:branches,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ], [
                'from_store_id.required' => 'Please select the source store.',
                'to_store_id.required' => 'Please select the destination store.',
                'items.required' => 'At least one product is required.',
                'items.*.product_id.required' => 'Please select a product.',
                'items.*.quantity.required' => 'Please enter the quantity.',
                'items.*.quantity.min' => 'Quantity must be at least 1.',
            ]);

            // Validate from and to store are different
            if ($request->from_store_id == $request->to_store_id) {
                return back()->withErrors(['to_store_id' => 'The destination store must be different from the source store.'])->withInput();
            }

            // Step 1: Pre-check inventory levels
            $errors = [];

            foreach ($request->items as $key => $item) {
                $availableQty = Inventory::where('product_id', $item['product_id'])
                    ->where('store_id', $request->from_store_id)
                    ->sum('quantity');

                if ($availableQty < $item['quantity']) {
                    $errors["items.$key.quantity"] = "Insufficient stock in source store. Available: $availableQty";
                }
            }

            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }

            // Step 2: Begin transaction and do actual stock transfer
            DB::beginTransaction();

            $prefix = 'TF';
            $datePart = now()->format('ymd'); // e.g., 250607
            $randomPart = str_pad(random_int(1, 99), 2, '0', STR_PAD_LEFT); // e.g., 06

            $transferNumber = "{$prefix}-{$datePart}-{$randomPart}";

            $arr_low_stock = [];
            foreach ($request->items as $item) {
                $remainingQty = $item['quantity'];

                $inventories = Inventory::where('product_id', $item['product_id'])
                    ->where('store_id', $request->from_store_id)
                    ->orderBy('expiry_date')
                    ->get();

                foreach ($inventories as $inventory) {
                    if ($remainingQty <= 0) break;

                    $low_qty_level = Inventory::lowLevelQty($item['product_id'], $request->from_store_id);

                    $total_qty = Inventory::countQty($item['product_id'], $request->from_store_id);

                    $deductQty = min($inventory->quantity, $remainingQty);

                    // Deduct from source store
                    $inventory->quantity -= $deductQty;
                    $inventory->save();

                    $total_qty = $total_qty - $deductQty;
                    // Add to destination store
                    $criteria = [
                        'store_id'    => $request->to_store_id,
                        'product_id'  => $item['product_id'],
                        'batch_no'    => $inventory->batch_no,
                        'expiry_date' => $inventory->expiry_date->toDateString(),
                    ];

                    $storeInventory = Inventory::where($criteria)->first();

                    if ($storeInventory) {
                        $storeInventory->quantity += $deductQty;
                        $storeInventory->save();
                    } else {
                        $low_qty_level_wh = Inventory::lowLevelQty($item['product_id'], 1);

                        Inventory::create([
                            'store_id'     => $request->to_store_id,
                            'location_id'  => $request->to_store_id,
                            'product_id'   => $item['product_id'],
                            'batch_no'     => $inventory->batch_no,
                            'expiry_date'  => $inventory->expiry_date->toDateString(),
                            'quantity'     => $deductQty,
                            'low_level_qty' => $low_qty_level_wh,
                        ]);
                    }


                    if ($total_qty < $low_qty_level) {
                        // $arr['id'] = $item['product_id'];
                        $arr_low_stock[$item['product_id']] = $item['product_id'];
                        // sendNotification('low_stock', 'Store stock request', $request->from_store_id, Auth::id(), json_encode($arr));
                        // sendNotification('low_stock', 'Store stock request', null, Auth::id(), json_encode($arr));
                    }

                    // Stock status changes and logs
                    stockStatusChange($item['product_id'], $request->from_store_id, $deductQty, 'transfer_stock');
                    stockStatusChange($item['product_id'], $request->to_store_id, $deductQty, 'add_stock');

                    $inventoryService = new \App\Services\InventoryService();
                    $inventoryService->transferProduct(
                        $item['product_id'],
                        $inventory->id,
                        $request->from_store_id,
                        $request->to_store_id,
                        $deductQty,
                        'store_to_store',
                        'store'
                    );

                    // Log the transfer
                    StockTransfer::create([
                        'stock_request_id' => $request->request_id ?? null,
                        'transfer_number' => $transferNumber,
                        'from_branch_id' => $request->from_store_id,
                        'to_branch_id' => $request->to_store_id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'status' => 'approved',
                        'transfer_by' => Auth::id(),
                        'transferred_at' => now(),
                    ]);

                    $remainingQty -= $deductQty;
                }
            }

            if (!empty($arr_low_stock)) {

                $arr['product_id'] =  implode(',', array_values($arr_low_stock));
                $arr['store_id'] =  (string) $request->from_store_id;

                sendNotification('low_stock', 'Some products are running low', $request->from_store_id, Auth::id(), json_encode($arr));
                sendNotification('low_stock', 'Some products are running low', null, Auth::id(), json_encode($arr));
            }

            // Send notification and commit
            $data['id'] = $transferNumber;
            $data['from_store'] = Branch::find($request->from_store_id)->name;
            $data['to_store'] = Branch::find($request->to_store_id)->name;

            if ($request->to_store_id != 1) {
                sendNotification('transfer_stock', 'Stock transfer completed successfully', 1, Auth::id(), json_encode($data), 0);
                sendNotification('transfer_stock', 'Stock transfer completed successfully', $request->to_store_id, Auth::id(), json_encode($data), 0);
            } else {
                sendNotification('transfer_stock', 'Stock transfer completed successfully', 1, Auth::id(), json_encode($data), 0);
            }

            DB::commit();

            return redirect()->route('inventories.list')->with('success', 'Stock has been transferred successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to transfer stock: ' . $e->getMessage())->withInput();
        }
    }
}
