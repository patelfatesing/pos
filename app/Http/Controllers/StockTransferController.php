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
        $stores = Branch::where('is_deleted', 'no')->get();
        $products = Product::all();
        $data = User::with('userInfo')
            ->where('users.id', Auth::id())
            ->where('is_deleted', 'no')
            ->firstOrFail();

        return view('stocks_transfer.create', compact('stores', 'products', 'data'));
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

            $transferNumber = 'TRF-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

            foreach ($request->items as $item) {
                $remainingQty = $item['quantity'];

                $inventories = Inventory::where('product_id', $item['product_id'])
                    ->where('store_id', $request->from_store_id)
                    ->orderBy('expiry_date')
                    ->get();

                foreach ($inventories as $inventory) {
                    if ($remainingQty <= 0) break;

                    $deductQty = min($inventory->quantity, $remainingQty);

                    // Deduct from source store
                    $inventory->quantity -= $deductQty;
                    $inventory->save();

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
                        Inventory::create([
                            'store_id'     => $request->to_store_id,
                            'location_id'  => $request->to_store_id,
                            'product_id'   => $item['product_id'],
                            'batch_no'     => $inventory->batch_no,
                            'expiry_date'  => $inventory->expiry_date->toDateString(),
                            'quantity'     => $deductQty,
                        ]);
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

            // Send notification and commit
            $data['id'] = $transferNumber;
            sendNotification('transfer_stock', 'Stock transfer completed successfully', $request->to_store_id, Auth::id(), json_encode($data), 0);

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
