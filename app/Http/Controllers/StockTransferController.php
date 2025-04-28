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
        $stores = Branch::all();
        $products = Product::all();
        $data = User::with('userInfo')
        ->where('users.id', Auth::id())
        ->where('is_deleted', 'no')
        ->firstOrFail();
    
        return view('stocks_transfer.create', compact('stores', 'products','data'));
    }

    
    public function store(Request $request)
    {

        // dd($request);
        $validated = $request->validate([
            'from_store_id' => 'required|exists:branches,id',
            'to_store_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            // 'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {

            $transferNumber = 'TRF-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

            foreach($request->items as $key => $val){

                // dd($val['product_id']);                    // Decrease from warehouse
                    $inventories = Inventory::where('product_id', $val['product_id'])->orderBy('expiry_date')->get(); // optional: FIFO

                    $totalQuantity = $inventories->sum('quantity');
                    
                    if ($totalQuantity < $val['product_id']) {
                        return response()->json([
                            'status' => 'error',
                            'message' => "Not enough stock for product"
                        ]);
                    }

                    $remainingQty = $val['product_id'];

                    
                    foreach ($inventories as $inventory) {
                        if ($remainingQty <= 0) break;

                        $deducted = min($inventory->quantity, $remainingQty);

                        // Deduct from warehouse
                        $inventory->quantity -= $deducted;
                        $inventory->save();

                        // Add to store inventory
                        $storeInventory = Inventory::firstOrNew([
                            'store_id' => $request->from_store_id,
                            'location_id'=> $request->to_store_id,
                            'product_id' => $val['product_id'],
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
                        
                        $inventoryService->transferProduct(
                            $val['product_id'],
                            $inventory->id,
                            $request->from_store_id,
                            $request->to_store_id,
                            $deducted,
                            'store_to_store',
                            'store'
                        );

                        $remainingQty -= $deducted;

                        StockTransfer::create([
                            'stock_request_id' => $request->request_id,
                            'transfer_number' => $transferNumber,
                            'from_branch_id' => $request->from_store_id,
                            'to_branch_id' => $request->to_store_id,
                            'product_id' => $val['product_id'],
                            'quantity' => $val['quantity'],
                            'status' => 'approved', // or 'completed' depending on flow
                            'transfer_by' => Auth::id(),
                            'transferred_at' => now(),
                        ]);

                    }
            }
            
            DB::commit();
            return redirect()->route('stock.requestList')->with('success', 'Stock request submitted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to submit stock request: ' . $e->getMessage());
        }
    }    


}
