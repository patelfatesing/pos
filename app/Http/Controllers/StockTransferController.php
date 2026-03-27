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
use App\Models\Category;
use App\Models\UserShift;
use Carbon\Carbon;

class StockTransferController extends Controller
{
    public function index()
    {
        $branch_id = '';
        if (isset($_GET['branch_id'])) {
            $branch_id = $_GET['branch_id'];
        }

        $shift_id = '';
        if (isset($_GET['shift_id'])) {
            $shift_id = $_GET['shift_id'];
        }

        if (auth()->user()->role_id == 1 || canDo(auth()->user()->role_id, 'stock-transfer-list')) {
            return view('stocks_transfer.list', compact('branch_id', 'shift_id'));
        } else {
            return view('errors.403', [
                'message' => 'You do not have permission to view this stock request.'
            ]);
        }
    }

    public function craeteTransfer()
    {
        $categories = Category::all();

        $stores = Branch::where('is_deleted', 'no')->get();
        $products = Product::all();
        $data = User::with('userInfo')
            ->where('users.id', Auth::id())
            ->where('is_deleted', 'no')
            ->firstOrFail();

        return view('stocks_transfer.create', compact('stores', 'products', 'data', 'categories'));
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
            DB::raw('MIN(stock_transfers.id) as id'), // ✅ use MIN id
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

            // ✅ GROUP ONLY BY TRANSFER
            ->groupBy([
                'stock_transfers.transfer_number',
                'stock_transfers.from_branch_id',
                'stock_transfers.to_branch_id',
                'stock_transfers.status',
                'stock_transfers.transfer_by',
                'stock_transfers.transferred_at',
                'from_branch.name',
                'to_branch.name',
                'users.name',
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

        if (!empty($request->branch_id)) {
            $query->where(function ($q) use ($request) {
                $q->where('stock_transfers.from_branch_id', $request->branch_id)
                    ->orWhere('stock_transfers.to_branch_id', $request->branch_id);
            });
        }

        if (!empty($request->shift_id)) {
            $query->where('stock_transfers.shift_id', $request->shift_id);
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

            $action = '<div class="d-flex align-items-center list-action">';
            $action .= '<a class="badge badge-info mr-2" data-toggle="tooltip" data-placement="top" title="View"
                        href="' . route('stock-transfer.view', $transfer->transfer_number) . '"><i class="ri-eye-line mr-0"></i></a>';


            $action .= '<a class="badge bg-success mr-2" title="Edit" href="' . url('/stock-transfer/edit/' . $transfer->id) . '">
                <i class="ri-pencil-line"></i></a>';
            $action .= '</div>';


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

            $running_shift = ShiftClosing::where('branch_id', $request->to_store_id)
                ->where('status', 'pending')
                ->first();


            if (!$running_shift) {            // null  ➔ destination store not open
                return back()
                    ->withErrors(['to_store_id' => 'The destination store is not open.'])
                    ->withInput();
            }

            $running_shift_form = ShiftClosing::where('branch_id', $request->to_store_id)
                ->where('status', 'pending')
                ->first();


            if (!$running_shift_form) {            // null  ➔ destination store not open
                return back()
                    ->withErrors(['from_store_id' => 'The from store is not open.'])
                    ->withInput();
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

            $today = Carbon::today();


            $arr_low_stock = [];
            foreach ($request->items as $item) {

                $remainingQty = $item['quantity'];

                $inventories = Inventory::where('product_id', $item['product_id'])
                    ->where('store_id', $request->from_store_id)
                    ->orderBy('expiry_date')
                    ->get();

                foreach ($inventories as $inventory) {

                    if ($remainingQty <= 0) break;

                    $deductQty = min($inventory->quantity, $remainingQty);

                    // ✅ deduct source
                    $inventory->quantity -= $deductQty;
                    $inventory->save();

                    // ✅ add destination
                    $dest = Inventory::where([
                        'store_id' => $request->to_store_id,
                        'product_id' => $item['product_id'],
                        'batch_no' => $inventory->batch_no,
                        'expiry_date' => optional($inventory->expiry_date)->toDateString(),
                    ])->first();

                    if ($dest) {
                        $dest->quantity += $deductQty;
                        $dest->save();
                    } else {
                        Inventory::create([
                            'store_id' => $request->to_store_id,
                            'location_id' => $request->to_store_id,
                            'product_id' => $item['product_id'],
                            'batch_no' => $inventory->batch_no,
                            'expiry_date' => optional($inventory->expiry_date)->toDateString(),
                            'quantity' => $deductQty,
                        ]);
                    }

                    $remainingQty -= $deductQty;
                }

                $currentShiftFrom = UserShift::whereDate('start_time', $today)->where(['branch_id' => $request->from_store_id])->where(['status' => "pending"])->first();
                $currentShiftTo = UserShift::whereDate('start_time', $today)->where(['branch_id' => $request->to_store_id])->where(['status' => "pending"])->first();

                stockStatusChange($item['product_id'], $request->from_store_id, $item['quantity'], 'transfer_stock', $currentShiftFrom->id);
                stockStatusChange($item['product_id'], $request->to_store_id, $item['quantity'], 'add_stock', $currentShiftTo->id);

                // ✅ ONLY ONCE PER PRODUCT
                StockTransfer::create([
                    'stock_request_id' => $request->request_id ?? null,
                    'transfer_number' => $transferNumber,
                    'from_branch_id' => $request->from_store_id,
                    'to_branch_id' => $request->to_store_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'], // full qty
                    'status' => 'approved',
                    'transfer_by' => Auth::id(),
                    'transferred_at' => now(),
                    'shift_id' => $running_shift->id,
                    'from_shift_id=>'
                ]);
            }

            // =========================
            // 🔥 PREPARE LOG DATA
            // =========================
            $newData = [];

            foreach ($request->items as $item) {
                $newData[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'from_store' => $request->from_store_id,
                    'to_store'   => $request->to_store_id,
                ];
            }

            // =========================
            // 🔥 SAVE ACTIVITY LOG
            // =========================
            logActivity(
                'stock_transfer',
                'created',
                'Stock transfer created successfully',
                [], // no old data in create
                [
                    'transfer_number' => $transferNumber,
                    'items' => $newData,
                    'total_items' => count($newData)
                ]
            );

            if (!empty($arr_low_stock)) {

                $arr['product_id'] =  implode(',', array_values($arr_low_stock));
                $arr['store_id'] =  (string) $request->from_store_id;

                sendNotification('low_stock', 'Some products have low level stock please check', $request->from_store_id, Auth::id(), json_encode($arr));
                sendNotification('low_stock', 'Some products have low level stock please check', null, Auth::id(), json_encode($arr));
            }

            // Send notification and commit
            $data['id'] = $transferNumber;
            $data['from_store'] = Branch::find($request->from_store_id)->name;
            $data['to_store'] = Branch::find($request->to_store_id)->name;
            $data['type'] = 'transfer_stock';

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

    public function edit($id)
    {
        // Get selected transfer row
        $transfer = StockTransfer::findOrFail($id);

        // Get ALL items of same transfer_number
        $items = StockTransfer::where('transfer_number', $transfer->transfer_number)->get();

        $categories = Category::all();

        $stores = Branch::where('is_deleted', 'no')->get();

        $products = Product::all();

        return view('stocks_transfer.edit', compact(
            'transfer',
            'items',
            'stores',
            'categories',
            'products'
        ));
    }

    public function update(Request $request, $id)
    {
        try {

            // ✅ Validation
            $request->validate([
                'from_store_id' => 'required|exists:branches,id',
                'to_store_id'   => 'required|exists:branches,id',
                'items'         => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity'   => 'required|integer|min:1',
            ]);

            if ($request->from_store_id == $request->to_store_id) {
                return back()->withErrors([
                    'to_store_id' => 'Source and destination must be different'
                ])->withInput();
            }

            DB::beginTransaction();

            // ✅ Get existing transfer group
            $transfer = StockTransfer::findOrFail($id);

            $oldTransfers = StockTransfer::where('transfer_number', $transfer->transfer_number)->get();

            // =========================
            // 🔁 STEP 1: REVERSE OLD STOCK
            // =========================
            foreach ($oldTransfers as $old) {

                $remainingQty = $old->quantity;

                // Add back to source
                Inventory::create([
                    'store_id'   => $old->from_branch_id,
                    'product_id' => $old->product_id,
                    'quantity'   => $old->quantity,
                    'location_id' => $old->from_branch_id
                ]);

                // Deduct from destination
                $destInventories = Inventory::where('product_id', $old->product_id)
                    ->where('store_id', $old->to_branch_id)
                    ->orderBy('expiry_date')
                    ->get();

                foreach ($destInventories as $inv) {
                    if ($remainingQty <= 0) break;

                    $deduct = min($inv->quantity, $remainingQty);
                    $inv->quantity -= $deduct;
                    $inv->save();

                    $remainingQty -= $deduct;
                }
            }

            // =========================
            // 🗑️ STEP 2: DELETE OLD RECORDS
            // =========================
            StockTransfer::where('transfer_number', $transfer->transfer_number)->delete();

            // =========================
            // 🆕 STEP 3: CREATE NEW TRANSFER (LIKE STORE)
            // =========================

            $transferNumber = $transfer->transfer_number; // keep same number

            $changes = []; // for log

            foreach ($request->items as $item) {

                $oldQty = $oldTransfers->where('product_id', $item['product_id'])->sum('quantity');

                $remainingQty = $item['quantity'];

                $inventories = Inventory::where('product_id', $item['product_id'])
                    ->where('store_id', $request->from_store_id)
                    ->orderBy('expiry_date')
                    ->get();

                foreach ($inventories as $inventory) {

                    if ($remainingQty <= 0) break;

                    $deductQty = min($inventory->quantity, $remainingQty);

                    // deduct source
                    $inventory->quantity -= $deductQty;
                    $inventory->save();

                    // add destination
                    $dest = Inventory::where([
                        'store_id' => $request->to_store_id,
                        'product_id' => $item['product_id'],
                        'batch_no' => $inventory->batch_no,
                        'expiry_date' => optional($inventory->expiry_date)->toDateString(),
                    ])->first();

                    if ($dest) {
                        $dest->quantity += $deductQty;
                        $dest->save();
                    } else {
                        Inventory::create([
                            'store_id' => $request->to_store_id,
                            'location_id' => $request->to_store_id,
                            'product_id' => $item['product_id'],
                            'batch_no' => $inventory->batch_no,
                            'expiry_date' => optional($inventory->expiry_date)->toDateString(),
                            'quantity' => $deductQty,
                        ]);
                    }

                    $remainingQty -= $deductQty;
                }

                // ✅ CREATE ONLY ONCE PER PRODUCT
                StockTransfer::create([
                    'transfer_number' => $transferNumber,
                    'from_branch_id' => $request->from_store_id,
                    'to_branch_id' => $request->to_store_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'status' => 'approved',
                    'transfer_by' => Auth::id(),
                    'transferred_at' => now(),
                ]);

                // log change
                $changes[] = [
                    'product_id' => $item['product_id'],
                    'old_qty' => $oldQty,
                    'new_qty' => $item['quantity'],
                ];
            }

            $oldData = $oldTransfers->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'from' => $item->from_branch_id,
                    'to' => $item->to_branch_id,
                ];
            })->toArray();

            $newData = [];

            foreach ($request->items as $item) {
                $newData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'from' => $request->from_store_id,
                    'to' => $request->to_store_id,
                ];
            }

            // =========================
            // 📝 STORE CHANGE LOG
            // =========================
            logActivity(
                'stock_transfer',
                'updated',
                'Stock transfer updated',
                $oldData,
                $newData
            );

            DB::commit();

            return redirect()->route('stock-transfer.list')
                ->with('success', 'Transfer updated successfully');
        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
