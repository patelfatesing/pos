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
    
        return view('stocks.create', compact('stores', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
            $stockRequest = StockRequest::create([
                'store_id' => $request->store_id,
                'requested_by' => Auth::id(),
                'notes' => $request->notes,
                'requested_at' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                StockRequestItem::create([
                    'stock_request_id' => $stockRequest->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('stock.index')->with('success', 'Stock request submitted successfully.');

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

    switch ($orderColumn) {
        case 'store':
            $orderColumn = 'stock_requests.id';
            break;
        case 'created_at':
            $orderColumn = 'stock_requests.updated_at';
            break;
        default:
            $orderColumn = 'stock_requests.' . $orderColumn;
    }
    $query = StockRequest::with(['branch', 'user']);

    if (!empty($searchValue)) {
        $query->where(function ($q) use ($searchValue) {
            $q->where('notes', 'like', "%$searchValue%")
              ->orWhereHas('store', fn($s) => $s->where('name', 'like', "%$searchValue%"))
              ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%$searchValue%"));
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
        $action .= "<form method='POST' action='" . $url . "/stock/view/" . $requestItem->id . "' style='display:inline;'>"
                 . csrf_field() . method_field('DELETE')
                 . "<button class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</button></form>";

                 if ($requestItem->status === 'pending') {
                    $action .= "<button class='btn btn-success btn-sm ml-1 open-approve-modal' data-id='{$requestItem->id}'>Approve</button>";

                    // $action .= "<button class='btn btn-success btn-sm approve-btn ml-1' data-id='{$requestItem->id}'>Approve</button>";
                }
        $records[] = [
            'id' => $requestItem->id,
            'store' => $requestItem->store->name ?? 'Warehouse',
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
            $warehouse = Inventory::whereNull('store_id')->where('product_id', $item->product_id)->first();
            if (!$warehouse || $warehouse->quantity < $updatedQty) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Not enough stock for product {$item->product->name}"
                ]);
            }
            // echo "<pre>";
            // print_r($warehouse->quantity);
            // echo "<br>";
            // echo $updatedQty;
            $warehouse->quantity -= $updatedQty;

            // dd($warehouse->quantity);
            $warehouse->save();

            // Increase to store
            $storeInventory = Inventory::firstOrNew([
                'store_id' => $stockRequest->store_id,
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