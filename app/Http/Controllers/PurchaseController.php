<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use App\Models\Product;
use App\Models\VendorList;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use App\Models\ShiftClosing;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('purchase.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendors = VendorList::where('is_active', 1)->get();
        $products = Product::select('id', 'name')->where('is_deleted', 'no')->get();

        // $products = Product::select('products.id', 'products.name', DB::raw('SUM(inventories.quantity) as total_quantity'))
        // ->join('inventories', 'products.id', '=', 'inventories.product_id')
        // ->where('products.is_deleted', 'no')
        // ->groupBy('products.id', 'products.name') // Include all selected fields
        // ->orderBy('inventories.id', 'asc')
        // ->get();

        return view('purchase.create', compact('vendors', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'vendor_id' => 'required|exists:vendor_lists,id',
            'bill_no' => 'required|string|max:255|unique:purchases,bill_no',
            'date' => 'required|date',
            'parchase_ledger' => 'required',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer',
            'products.*.brand_name' => 'required|string',
            'products.*.batch' => 'required|string',
            'products.*.mfg_date' => 'required|date',
            'products.*.mrp' => 'required|numeric',
            'products.*.qnt' => 'required|integer|min:1',
            'products.*.rate' => 'required|numeric',
            'products.*.amount' => 'required|numeric',
        ]);

        $running_shift = ShiftClosing::where('branch_id', 1)
            ->where('status', 'pending')
            ->first();

        if (!$running_shift) {            // null  ➔ destination store not open
            return back()
                ->withErrors(['to_store_id' => 'The Warehouse is not open.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $purchase = Purchase::create([
                'bill_no' => $request->bill_no,
                'vendor_id' => $request->vendor_id,
                'parchase_ledger' => $request->parchase_ledger,
                'total' => $request->total,
                'date' => $request->date,
                'excise_fee' => $request->excise_fee ?? 0,
                'composition_vat' => $request->composition_vat ?? 0,
                'surcharge_on_ca' => $request->surcharge_on_ca ?? 0,
                'tcs' => $request->tcs ?? 0,
                'aed_to_be_paid' => $request->aed_to_be_paid ?? 0,
                'total_amount' => $request->total_amount,
                'vat' => $request->vat,
                'surcharge_on_vat' => $request->surcharge_on_vat,
                'blf' => $request->blf,
                'permit_fee' => $request->permit_fee,
                'rsgsm_purchase' => $request->rsgsm_purchase,
                'case_purchase' => $request->case_purchase,
                'case_purchase_per' => $request->case_purchase_per,
                'case_purchase_amt' => $request->case_purchase_amt,
                'status' => $request->status ?? 'pending',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->products as $product) {
                PurchaseProduct::create([
                    'brand_name' => $product['brand_name'],
                    'batch' => $product['batch'],
                    'mfg_date' => $product['mfg_date'],
                    'mrp' => $product['mrp'],
                    'qnt' => $product['qnt'],
                    'rate' => $product['rate'],
                    'amount' => $product['amount'],
                    'purchase_id' => $purchase->id
                ]);

                // $user_id = Auth::id();

                // $user_details = UserInfo::select('branch_id')
                // ->where('user_id', $user_id)
                // ->firstOrFail();

                $product_id = $product['product_id'];
                $batch = $product['batch'];
                $expiryDatePlusOneYear = Carbon::parse($product['mfg_date'])->addYear();

                $store_id = 1; // Assuming store_id is always 1 for this example, adjust as needed
                $record = Product::with(['inventorieUnfiltered' => function ($query) use ($store_id) {
                    $query->where('store_id', $store_id);
                }])->where('id', $product_id)->where('is_deleted', 'no')->firstOrFail();

                $inventoryService = new \App\Services\InventoryService();

                if (!empty($record->inventorieUnfiltered)) {

                    // $product['qnt'] = $product['qnt'] + $record->inventorie[0]->quantity;

                    $batchNumber = strtoupper($request->sku) . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));
                    if ($record->inventorieUnfiltered->batch_no == $batch) {

                        $inventory = Inventory::findOrFail($record->inventorieUnfiltered->id);

                        $qnt =  $product['qnt'] + $inventory->quantity;
                        $inventory->updated_at = now();
                        $inventory->quantity = $qnt;
                        // $inventory->quantity = $qnt;
                        $inventory->save();

                        stockStatusChange($product_id, 1, $product['qnt'], 'add_stock');
                        $inventoryService->transferProduct($product_id, $inventory->id, 1, '', $qnt, 'add_stock');
                    } else {

                        $inventory = Inventory::firstOrCreate([
                            'product_id'  => $product_id,
                            'store_id'    => 1,
                            'location_id'    => 1,
                            'batch_no'    => $batch,
                            'expiry_date' => $expiryDatePlusOneYear,
                            'quantity' => $product['qnt'],
                            'added_by' => Auth::id(),
                        ]);
                        stockStatusChange($product_id, 1, $product['qnt'], 'add_stock');
                        $inventoryService->transferProduct($product_id, $inventory->id, 1, '', $product['qnt'], 'add_stock');
                    }

                    $inventoryService->transferProduct($product_id, $inventory->id, 1, '', $product['qnt'], 'add_stock');
                } else {


                    $inventory = Inventory::firstOrCreate([
                        'product_id'  => $product_id,
                        'store_id'    => 1,
                        'location_id'    => 1,
                        'batch_no'    => $batch,
                        'expiry_date' => $expiryDatePlusOneYear,
                        'added_by' => Auth::id(),
                        'quantity' => $product['qnt']
                    ]);

                    stockStatusChange($product_id, 1, $product['qnt'], 'add_stock');
                    $inventoryService->transferProduct($product_id, $inventory->id, 1, '', $product['qnt'], 'add_stock');
                }
            }

            DB::commit();

            return redirect()->route('purchase.list')->with('success', 'Delivery has been successfully added.');
            // return response()->json([
            //     'message' => 'Purchase order created successfully.',
            //     'purchase' => $purchase->load('products')
            // ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to create purchase order.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $request->input("columns.$orderColumnIndex.data", 'id');
        $orderDirection = $request->input('order.0.dir', 'desc');

        // Map frontend column names to actual DB columns
        switch ($orderColumn) {
            case 'party_name':
                $orderColumn = 'vendor_lists.name';
                break;
            case 'bill_no':
                $orderColumn = 'purchases.bill_no';
                break;
            case 'created_at':
                $orderColumn = 'purchases.created_at';
                break;
            default:
                $orderColumn = 'purchases.created_at';
        }

        // Join vendors table to support ordering by vendor name
        $query = Purchase::select('purchases.*', 'vendor_lists.name as vendor_name')
            ->leftJoin('vendor_lists', 'purchases.vendor_id', '=', 'vendor_lists.id');

        // Search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('purchases.bill_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('vendor_lists.name', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsTotal = Purchase::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $records = [];

        foreach ($data as $purchase) {
            $records[] = [
                'bill_no' => $purchase->bill_no,
                'party_name' => $purchase->vendor_name ?? 'N/A',
                'total' => number_format($purchase->total, 2),
                'total_amount' => number_format($purchase->total_amount, 2),
                'created_at' => date('d-m-Y h:i', strtotime($purchase->created_at)),
                'action' => ' <div class="d-flex align-items-center list-action">
                            <a class="badge badge-info mr-2" data-toggle="tooltip" title="View"
                                href="' . url('/purchase/view/' . $purchase->id) . '"><i class="ri-eye-line mr-0"></i></a>
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

    /**
     * Show the form for editing the specified resource.
     */
    public function getProductDetails(string $id)
    {
        // $record = Product::with('inventories')->where('id', $id)->where('is_deleted', 'no')->firstOrFail();

        $record = Product::select(
            'products.id',
            'products.name',
            'products.size',
            'products.brand',
            'products.mrp',
            'inventories.batch_no',
            'inventories.mfg_date',
            'products.cost_price',
            'products.sell_price',
            DB::raw('SUM(COALESCE(inventories.quantity, 0)) as total_quantity')
        )
            ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('products.is_deleted', 'no')
            ->where('products.id', $id)
            ->groupBy(
                'products.id',
                'products.name',
                'products.brand',
                'inventories.batch_no',
                'inventories.mfg_date',
                'products.cost_price',
                'products.sell_price',
                'products.size',
                'products.mrp'
            )
            ->orderBy('total_quantity', 'asc')
            ->first();

        return json_decode($record);
    }

    public function view($id)
    {
        $purchase = Purchase::with('vendor', 'productsItems')->findOrFail($id);

        return view('purchase.view', compact('purchase'));
    }

    public function getVendorProducts($vendorId)
    {
        // Define vendor → subcategory mapping
        $map = [
            1 => [1, 2],   // vendor 1 → subcategories 1,2
            2 => [2, 3],   // vendor 2 → subcategories 2,3
        ];

        $allowed = $map[$vendorId] ?? [];

        $products = Product::select('id', 'name', 'subcategory_id')
            ->where('is_deleted', 'no')
            ->when(!empty($allowed), function ($q) use ($allowed) {
                $q->whereIn('subcategory_id', $allowed);
            })
            ->get();

        return response()->json($products);
    }
}
