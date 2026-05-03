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

        $type = '';
        if (isset($_GET['type'])) {
            $type = $_GET['type'];
        }

        if (auth()->user()->role_id == 1 || canDo(auth()->user()->role_id, 'stock-transfer-list')) {
            return view('stocks_transfer.list', compact('branch_id', 'shift_id', 'type'));
        } else {
            return view('errors.403', [
                'message' => 'You do not have permission to view this stock request.'
            ]);
        }
    }

    public function craeteTransfer()
    {
        $categories = Category::all();

        $shift_id = isset($_GET['shift_id']);

        $shift = '';
        if (!empty($shift_id)) {
            $shift = ShiftClosing::findOrFail($shift_id);
        }

        $stores = Branch::where('is_deleted', 'no')->get();
        $products = Product::all();
        $data = User::with('userInfo')
            ->where('users.id', Auth::id())
            ->where('is_deleted', 'no')
            ->firstOrFail();

        return view('stocks_transfer.create', compact('stores', 'products', 'data', 'categories', 'shift_id', 'shift'));
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

    public function modalList(Request $request)
    {
        return view('stocks_transfer.partials.transfer-list', [
            'branch_id' => $request->branch_id,
            'shift_id'  => $request->shift_id,
            'type'      => $request->type ?? 'admin',
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

            $shift = ShiftClosing::select('start_time')->findOrFail($request->shift_id);

            // Extract only date from shift start_time
            $date = \Carbon\Carbon::parse($shift->start_time)->format('Y-m-d');

            $query->whereDate('stock_transfers.transferred_at', $date);
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


            $action .= '<a class="badge bg-success mr-2" title="Edit" href="' . url('/stock-transfer/edit/' . $transfer->id) . '?type=admin">
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

    public function getTransferDataNew(Request $request)
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

            $shift = ShiftClosing::select('start_time')->findOrFail($request->shift_id);

            // Extract only date from shift start_time
            $date = \Carbon\Carbon::parse($shift->start_time)->format('Y-m-d');

            $query->whereDate('stock_transfers.shift_id', $request->shift_id);
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

            // ✅ VIEW (MODAL)
            $action .= '<a class="badge badge-info mr-2"
                href="javascript:void(0)"
                onclick="openViewTransfer(\'' . $transfer->transfer_number . '\')"
                title="View">
                <i class="ri-eye-line mr-0"></i>
            </a>';

            // ✅ EDIT (MODAL)
            $action .= '<a class="badge bg-success mr-2"
                href="javascript:void(0)"
                onclick="openEditTransfer(' . $transfer->id . ')"
                title="Edit">
                <i class="ri-pencil-line"></i>
            </a>';

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
            $validated = $request->validate([
                'from_store_id' => 'required|exists:branches,id',
                'to_store_id' => 'required|exists:branches,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            // ❌ SAME STORE CHECK
            if ($request->from_store_id == $request->to_store_id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['to_store_id' => ['From and To store must be different']]
                    ], 422);
                }
                return back()->withErrors(['to_store_id' => 'From and To store must be different']);
            }

            DB::beginTransaction();

            $shift_id = $request->shift_id;
            $shift_date = $request->date;

            $datePart = now()->format('ymd');
            $prefix = 'TF';
            if (empty($shift_id)) {

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


                $datePart = now()->format('ymd'); // e.g., 250607

                $currentShiftFrom = UserShift::where('branch_id', $request->from_store_id)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();

                $currentShiftTo = UserShift::where('branch_id', $request->to_store_id)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();

                if (!$currentShiftFrom || !$currentShiftTo) {
                    DB::rollback();
                    return back()->with('error', 'Shift not found for selected date.');
                }
            } else {

                $shift_date = Carbon::parse($request->date)->toDateString();
                $currentShiftTo = UserShift::where('branch_id', $request->to_store_id)
                    ->whereDate('start_time', $shift_date)
                    ->latest()
                    ->first();

                $currentShiftFrom = UserShift::where('branch_id', $request->from_store_id)
                    ->whereDate('start_time', $shift_date)
                    ->latest()
                    ->first();

                if (!$currentShiftFrom || !$currentShiftTo) {
                    DB::rollback();
                    return back()->with('error', 'Shift not found for selected date.');
                }
                $datePart = Carbon::parse($shift_date)->format('ymd');
            }

            $randomPart = str_pad(random_int(1, 99), 2, '0', STR_PAD_LEFT); // e.g., 06
            $transferNumber = "{$prefix}-{$datePart}-{$randomPart}";

            foreach ($request->items as $item) {

                $availableQty = Inventory::where('product_id', $item['product_id'])
                    ->where('store_id', $request->from_store_id)
                    ->sum('quantity');

                if ($availableQty < $item['quantity']) {

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => false,
                            'errors' => [
                                'items' => ["Insufficient stock for product ID {$item['product_id']}"]
                            ]
                        ], 422);
                    }

                    return back()->withErrors(['items' => 'Insufficient stock']);
                }
            }

            foreach ($request->items as $item) {

                Inventory::where('product_id', $item['product_id'])
                    ->where('store_id', $request->from_store_id)
                    ->decrement('quantity', $item['quantity']);

                Inventory::updateOrCreate(
                    [
                        'store_id' => $request->to_store_id,
                        'location_id' => $request->to_store_id,
                        'product_id' => $item['product_id']
                    ],
                    [
                        'quantity' => DB::raw("quantity + {$item['quantity']}")
                    ]
                );

                StockTransfer::create([
                    'transfer_number' => $transferNumber,
                    'from_branch_id' => $request->from_store_id,
                    'to_branch_id' => $request->to_store_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'status' => 'approved',
                    'transfer_by' => Auth::id(),
                    'shift_id' => $shift_id,
                    'transferred_at' => now()
                ]);
            }

            DB::commit();

            // ✅ AJAX RESPONSE (MOST IMPORTANT)
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Transfer created successfully',
                    'shift_id' => $shift_id
                ]);
            }

            return redirect()->back()->with('success', 'Transfer created');
        } catch (\Illuminate\Validation\ValidationException $e) {

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'errors' => $e->errors()
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {

            DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', $e->getMessage());
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

            $request->validate([
                'from_store_id' => 'required|exists:branches,id',
                'to_store_id'   => 'required|exists:branches,id',
                'items'         => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity'   => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            $transfer = StockTransfer::findOrFail($id);

            $oldTransfers = StockTransfer::where('transfer_number', $transfer->transfer_number)->get();

            $oldShiftTo   = $oldTransfers->first()->shift_id;
            $oldShiftFrom = $oldTransfers->first()->from_shift_id;

            $transferDate = \Carbon\Carbon::parse($oldTransfers->first()->transferred_at)->toDateString();

            // =========================
            // 🔍 FIND REMOVED PRODUCTS
            // =========================
            $oldProductIds = $oldTransfers->pluck('product_id')->toArray();
            $newProductIds = collect($request->items)->pluck('product_id')->toArray();

            $removedProducts = array_diff($oldProductIds, $newProductIds);

            $affectedProducts = [];

            // =========================
            // 🔴 REMOVE PRODUCTS
            // =========================
            foreach ($oldTransfers as $old) {

                if (in_array($old->product_id, $removedProducts)) {

                    $remainingQty = $old->quantity;
                    $affectedProducts[] = $old->product_id; // ✅ ADD THIS

                    // add back to source
                    Inventory::create([
                        'store_id'   => $old->from_branch_id,
                        'product_id' => $old->product_id,
                        'quantity'   => $old->quantity,
                        'location_id' => $old->from_branch_id
                    ]);

                    // deduct from destination
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

                    // reverse daily stock
                    stockStatusChange($old->product_id, $old->from_branch_id, $old->quantity, 'add_stock', $oldShiftFrom, '', $transferDate);
                    stockStatusChange($old->product_id, $old->to_branch_id, $old->quantity, 'transfer_stock', $oldShiftTo, '', $transferDate);

                    // delete row
                    $old->delete();
                }
            }

            // =========================
            // 🟢 UPDATE EXISTING PRODUCTS
            // =========================
            foreach ($request->items as $item) {

                $existing = $oldTransfers->where('product_id', $item['product_id'])->first();

                if (!$existing) continue;

                $oldQty = $existing->quantity;
                $newQty = $item['quantity'];

                if ($oldQty == $newQty) continue;

                $diff = $newQty - $oldQty;
                $affectedProducts[] = $item['product_id']; // ✅ ADD THIS

                // 🔺 INCREASE
                if ($diff > 0) {

                    $remainingQty = $diff;

                    $inventories = Inventory::where('product_id', $item['product_id'])
                        ->where('store_id', $existing->from_branch_id)
                        ->orderBy('expiry_date')
                        ->get();

                    foreach ($inventories as $inventory) {

                        if ($remainingQty <= 0) break;

                        $deductQty = min($inventory->quantity, $remainingQty);

                        $inventory->quantity -= $deductQty;
                        $inventory->save();

                        $dest = Inventory::where([
                            'store_id' => $existing->to_branch_id,
                            'product_id' => $item['product_id'],
                            'batch_no' => $inventory->batch_no,
                            'expiry_date' => optional($inventory->expiry_date)->toDateString(),
                        ])->first();

                        if ($dest) {
                            $dest->quantity += $deductQty;
                            $dest->save();
                        }

                        $remainingQty -= $deductQty;
                    }

                    stockStatusChange($item['product_id'], $existing->from_branch_id, $diff, 'transfer_stock', $oldShiftFrom, '', $transferDate);
                    stockStatusChange($item['product_id'], $existing->to_branch_id, $diff, 'add_stock', $oldShiftTo, '', $transferDate);
                }

                // 🔻 DECREASE
                elseif ($diff < 0) {

                    $diff = abs($diff);

                    Inventory::create([
                        'store_id'   => $existing->from_branch_id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $diff,
                        'location_id' => $existing->from_branch_id
                    ]);

                    $remainingQty = $diff;

                    $destInventories = Inventory::where('product_id', $item['product_id'])
                        ->where('store_id', $existing->to_branch_id)
                        ->orderBy('expiry_date')
                        ->get();

                    foreach ($destInventories as $inv) {
                        if ($remainingQty <= 0) break;

                        $deduct = min($inv->quantity, $remainingQty);
                        $inv->quantity -= $deduct;
                        $inv->save();

                        $remainingQty -= $deduct;
                    }

                    stockStatusChange($item['product_id'], $existing->from_branch_id, $diff, 'add_stock', $oldShiftFrom, '', $transferDate);
                    stockStatusChange($item['product_id'], $existing->to_branch_id, $diff, 'transfer_stock', $oldShiftTo, '', $transferDate);
                }

                // update record
                $existing->update([
                    'quantity' => $newQty
                ]);
            }


            $affectedProducts = array_unique($affectedProducts);

            if (!empty($affectedProducts)) {

                foreach ($affectedProducts as $pid) {

                    // FROM branch
                    recalculateStockFromDateTransfer(
                        $pid,
                        $request->from_store_id,
                        $transferDate
                    );

                    // TO branch
                    recalculateStockFromDateTransfer(
                        $pid,
                        $request->to_store_id,
                        $transferDate
                    );
                }
            }

            DB::commit();

            if ($request->type == 'admin') {
                // ✅ AJAX RESPONSE (MOST IMPORTANT)
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Transfer created successfully',
                        'shift_id' => $transfer->shift_id
                    ]);
                }
                return redirect()->route('sales.salas-report')
                    ->with('success', 'Transfer updated successfully');
            } else {
                // ✅ AJAX RESPONSE (MOST IMPORTANT)
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Transfer created successfully',
                        'shift_id' => $transfer->shift_id
                    ]);
                }
                return redirect()->route('stock-transfer.list')
                    ->with('success', 'Transfer updated successfully');
            }
        } catch (\Exception $e) {

            DB::rollback();
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function createTransferModal(Request $request)
    {
        // DEBUG (keep for now)
        // dd($request->all());

        $categories = Category::all();

        $shift_id = $request->shift_id; // ✅ FIX

        $shift = null;

        if (!empty($shift_id)) {
            $shift = ShiftClosing::find($shift_id);
        }

        $stores = Branch::where('is_deleted', 'no')->get();
        $products = Product::all();

        $data = User::with('userInfo')
            ->where('users.id', Auth::id())
            ->where('is_deleted', 'no')
            ->firstOrFail();

        return view('stocks_transfer.partials.add-modal', compact(
            'stores',
            'products',
            'data',
            'categories',
            'shift_id',
            'shift'
        ));
    }

    public function editTransferModal($id)
    {
        $transfer = StockTransfer::findOrFail($id);

        $items = StockTransfer::where('transfer_number', $transfer->transfer_number)->get();

        $categories = Category::all();
        $stores = Branch::where('is_deleted', 'no')->get();
        $products = Product::all();

        return view('stocks_transfer.partials.edit-modal', compact(
            'transfer',
            'items',
            'stores',
            'categories',
            'products'
        ));
    }

    public function viewTransferModal($transferNumber)
    {
        $stockTransfer = new StockTransfer();
        $transfer = $stockTransfer->getTransferDetails($transferNumber);

        if (!$transfer) {
            abort(404);
        }

        return view('stocks_transfer.partials.view-modal', [
            'stockTransfer' => $transfer,
            'transferProducts' => $transfer->products
        ]);
    }
}
