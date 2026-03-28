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
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\PurchaseLedger;
use App\Models\SubCategory;
use App\Models\Accounting\{Voucher, VoucherLine, AccountLedger};
use Illuminate\Validation\Rule;

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
    public function create(Request $request)
    {
        $vendors = VendorList::where('is_active', 1)->get();
        $products = Product::select('id', 'name')->where('is_deleted', 'no')->get();
        $expMainCategory = ExpenseCategory::where('expense_type_id', 1)->get();
        $purchaseLedger = PurchaseLedger::where('is_active', 'Yes')->get();
        $subcategories = DB::table('sub_categories')->where('is_deleted', 'no')->get();

        $subcategoryId = request()->subcategory_id ?? null;

        $purchaseGroupNames = ['Purchase Ledger', 'Purchase Ledgers', 'Purchase Accounts'];
        // $vendorGroupNames = ['Sundry Creditors', 'Sundry Debtors'];
        $vendorGroupNames = ['Sundry Creditors'];

        $ledgers = \DB::table('account_ledgers as l')
            ->join('account_groups as g', 'g.id', '=', 'l.group_id')
            ->whereIn('g.name', $purchaseGroupNames)
            ->where(function ($q) {
                $q->where('l.is_deleted', 'No')->orWhereNull('l.is_deleted');
            })
            ->where(function ($q) {
                // handle boolean or enum
                $q->where('l.is_active', 1)->orWhere('l.is_active', 'Yes');
            })
            ->orderBy('l.name')
            ->get(['l.id', 'l.name']);

        $ledgersAll = \DB::table('account_ledgers as l')
            ->join('account_groups as g', 'g.id', '=', 'l.group_id')
            ->whereIn('g.name', $vendorGroupNames)
            ->where(function ($q) {
                $q->where('l.is_deleted', 'No')->orWhereNull('l.is_deleted');
            })
            ->where(function ($q) {
                // handle boolean or enum
                $q->where('l.is_active', 1)->orWhere('l.is_active', 'Yes');
            })
            ->orderBy('l.name')
            ->get(['l.id', 'l.name']);

        // $products = Product::select('products.id', 'products.name', DB::raw('SUM(inventories.quantity) as total_quantity'))
        // ->join('inventories', 'products.id', '=', 'inventories.product_id')
        // ->where('products.is_deleted', 'no')
        // ->groupBy('products.id', 'products.name') // Include all selected fields
        // ->orderBy('inventories.id', 'asc')
        // ->get();

        return view('purchase.create', compact('subcategories', 'vendors', 'products', 'expMainCategory', 'purchaseLedger', 'ledgers', 'ledgersAll'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'vendor_id' => 'required',
            'vendor_new_id' => 'required',
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

        // if (!$running_shift) {
        //     return back()
        //         ->withErrors(['to_store_id' => 'The Warehouse is not open.'])
        //         ->withInput();
        // }

        if (!$running_shift) {
            return back()
                ->with('warehouse_error', 'Warehouse shift is not open, Please check.')
                ->withInput();
        }

        DB::beginTransaction();

        $permitExcise   = (float) ($request->permit_fee_excise ?? 0);
        $vendExcise     = (float) ($request->vend_fee_excise ?? 0);
        $compositeExcise = (float) ($request->composite_fee_excise ?? 0);
        $exciseTotal    = (float) ($request->excise_total_amount ?? 0);
        $excise_fee    = (float) ($request->excise_fee ?? 0);
        $surcharge_on_ca    = (float) ($request->surcharge_on_ca ?? 0);
        $case_purchase_amt =        (float) ($request->case_purchase_amt ?? 0);
        $excise_80 = (float) ($request->excise_duty_80 ?? 0);
        $excise_20 = (float) ($request->excise_duty_20 ?? 0);

        $loading = (float) ($request->loading_charges ?? 0);

        try {
            // ----------------- 1) SAVE PURCHASE MASTER -----------------
            $purchase = Purchase::create([
                'bill_no' => $request->bill_no,
                'vendor_id' => $request->vendor_id,
                'vendor_new_id' => $request->vendor_new_id,
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
                'guarantee_fulfilled' => $request->guarantee_fulfilled ?? 0,
                'rsgsm_purchase' => $request->rsgsm_purchase,
                'case_purchase' => $request->case_purchase,
                'case_purchase_per' => $request->case_purchase_per,
                'case_purchase_amt' => $request->case_purchase_amt,
                'status' => $request->status ?? 'pending',
                'created_by' => Auth::id(),
                'permit_fee_excise'     => $permitExcise,
                'vend_fee_excise'       => $vendExcise,
                'composite_fee_excise'  => $compositeExcise,
                'excise_total_amount'   => $exciseTotal,
                'loading_charges' => $loading,
                'itp_value' => $request->itp_value
            ]);

            // ----------------- 2) SAVE PRODUCTS + INVENTORY -----------------
            foreach ($request->products as $product) {

                $product_id = $product['product_id'];
                PurchaseProduct::create([
                    'brand_name' => $product['brand_name'],
                    'batch' => $product['batch'],
                    'mfg_date' => $product['mfg_date'],
                    'mrp' => $product['mrp'],
                    'qnt' => $product['qnt'],
                    'rate' => $product['rate'],
                    'amount' => $product['amount'],
                    'purchase_id' => $purchase->id,
                    'product_id' => $product_id
                ]);

                $batch = $product['batch'];
                $expiryDatePlusOneYear = Carbon::parse($product['mfg_date'])->addYear();

                $store_id = 1; // Warehouse
                $record = Product::with(['inventorieUnfiltered' => function ($query) use ($store_id) {
                    $query->where('store_id', $store_id);
                }])
                    ->where('id', $product_id)
                    ->where('is_deleted', 'no')
                    ->firstOrFail();

                $inventoryService = new \App\Services\InventoryService();

                if (!empty($record->inventorieUnfiltered)) {

                    $batchNumber = strtoupper($request->sku) . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));

                    if ($record->inventorieUnfiltered->batch_no == $batch) {

                        $inventory = Inventory::findOrFail($record->inventorieUnfiltered->id);

                        $qnt = $product['qnt'] + $inventory->quantity;
                        $inventory->updated_at = now();
                        $inventory->quantity = $qnt;
                        $inventory->save();

                        stockStatusChange($product_id, 1, $product['qnt'], 'add_stock');
                        $inventoryService->transferProduct($product_id, $inventory->id, 1, '', $qnt, 'add_stock');
                    } else {
                        $inventory = Inventory::firstOrCreate([
                            'product_id'  => $product_id,
                            'store_id'    => 1,
                            'location_id' => 1,
                            'batch_no'    => $batch,
                            'expiry_date' => $expiryDatePlusOneYear,
                            'quantity'    => $product['qnt'],
                            'added_by'    => Auth::id(),
                        ]);

                        stockStatusChange($product_id, 1, $product['qnt'], 'add_stock');
                        $inventoryService->transferProduct($product_id, $inventory->id, 1, '', $product['qnt'], 'add_stock');
                    }

                    // This second transfer looks redundant but keeping as per your original logic
                    $inventoryService->transferProduct($product_id, $inventory->id, 1, '', $product['qnt'], 'add_stock');
                } else {

                    $inventory = Inventory::firstOrCreate([
                        'product_id'  => $product_id,
                        'store_id'    => 1,
                        'location_id' => 1,
                        'batch_no'    => $batch,
                        'expiry_date' => $expiryDatePlusOneYear,
                        'added_by'    => Auth::id(),
                        'quantity'    => $product['qnt']
                    ]);

                    stockStatusChange($product_id, 1, $product['qnt'], 'add_stock');
                    $inventoryService->transferProduct($product_id, $inventory->id, 1, '', $product['qnt'], 'add_stock');
                }
            }

            // ----------------- 3) CREATE ACCOUNTING VOUCHER (Purchase) -----------------

            // 3.1 Vendor info
            $vendor = VendorList::findOrFail($request->vendor_id);

            if (!$request->parchase_ledger) {
                throw new \Exception('Vendor/Purchase ledger is not selected.');
            }

            // In your current design, same ledger id used for Purchase & Vendor.
            // Later you can split them if needed.
            $purchaseLedgerId = (int) $request->parchase_ledger; // Purchase A/c (Dr)
            $vendorLedgerId   = (int) $request->vendor_new_id; // Vendor (Cr)

            // Base & grand total
            $baseAmount  = (float) ($request->total ?? 0);          // goods value
            $grandAmount = (float) ($request->total_amount ?? 0);   // final bill

            if ($grandAmount <= 0) {
                throw new \Exception('Invalid purchase total amount for voucher posting.');
            }

            // Extra components (will post separate Dr lines)
            $aed         = (float) ($request->aed_to_be_paid ?? 0);
            $gFull       = (float) ($request->guarantee_fulfilled ?? 0);
            $tcs         = (float) ($request->tcs ?? 0);
            $vat         = (float) ($request->vat ?? 0);
            $surVat      = (float) ($request->surcharge_on_vat ?? 0);
            $blf         = (float) ($request->blf ?? 0);
            $permit      = (float) ($request->permit_fee ?? 0);
            $rsgsm       = (float) ($request->rsgsm_purchase ?? 0);

            $loading_charges = (float) ($request->loading_charges ?? 0);

            // 3.2 Voucher header (like Tally Purchase)
            $baseAmount  = (float) $request->total;
            $grandAmount = (float) $request->total_amount;

            $voucher = Voucher::create([
                'gen_id'          => $purchase->id,
                'voucher_date'    => $request->date,
                'voucher_type'    => 'Purchase',
                'ref_no'          => $request->bill_no,
                'branch_id'       => $running_shift->branch_id,
                'narration'       => 'Purchase bill no ' . $request->bill_no,
                'created_by'      => Auth::id(),
                'party_ledger_id' => (int) $request->parchase_ledger,
                'sub_total'       => $baseAmount,
                'discount'        => 0,
                'tax'             => $vat + $surVat,
                'grand_total'     => $grandAmount,
                'admin_status'   => 'verify'
            ]);

            // 3.3 Dr Purchase (basic goods)
            VoucherLine::create([
                'voucher_id'     => $voucher->id,
                'ledger_id'      => $purchaseLedgerId,
                'dc'             => 'Dr',
                'amount'         => $baseAmount,
                'line_narration' => 'Purchase - ' . $request->bill_no,
            ]);

            // 3.4 Dr charges/Taxes – only when amount > 0

            if ($aed > 0) {
                $l = AccountLedger::where('name', 'AED TO BE PAID')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Dr',
                        'amount'         => $aed,
                        'line_narration' => 'AED TO BE PAID - ' . $request->bill_no,
                    ]);
                }
            }

            if ($tcs > 0) {
                $l = AccountLedger::where('name', 'TCS')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Dr',
                        'amount'         => $tcs,
                        'line_narration' => 'TCS - ' . $request->bill_no,
                    ]);
                }
            }

            if ($vat > 0) {
                $l = AccountLedger::where('name', 'VAT')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Dr',
                        'amount'         => $vat,
                        'line_narration' => 'VAT - ' . $request->bill_no,
                    ]);
                }
            }

            if ($surVat > 0) {
                $l = AccountLedger::where('name', 'SURCHARGE ON VAT')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Dr',
                        'amount'         => $surVat,
                        'line_narration' => 'SURCHARGE ON VAT - ' . $request->bill_no,
                    ]);
                }
            }

            if ($blf > 0) {
                $l = AccountLedger::where('name', 'BLF')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Cr',
                        'amount'         => $blf,
                        'line_narration' => 'BLF - ' . $request->bill_no,
                    ]);
                }
            }

            if ($excise_fee > 0) {
                $l = AccountLedger::where('name', 'EXCISE FEE')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Dr',
                        'amount'         => $excise_fee,
                        'line_narration' => 'EXCISE FEE - ' . $request->bill_no,
                    ]);
                }
            }

            if ($loading_charges > 0) {
                $l = AccountLedger::where('name', 'Loading Charges')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Dr',
                        'amount'         => $loading_charges,
                        'line_narration' => 'Loading Charges - ' . $request->bill_no,
                    ]);
                }
            }

            if ($compositeExcise > 0) {
                $l = AccountLedger::where('name', 'COMPOSITION VAT')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Dr',
                        'amount'         => $compositeExcise,
                        'line_narration' => 'COMPOSITION VAT - ' . $request->bill_no,
                    ]);
                }
            }

            if ($surcharge_on_ca > 0) {
                $l = AccountLedger::where('name', 'SURCHARGE ON CA')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Dr',
                        'amount'         => $surcharge_on_ca,
                        'line_narration' => 'SURCHARGE ON CA - ' . $request->bill_no,
                    ]);
                }
            }

            // Excise Duty 80%
            if ($excise_80 > 0) {
                $l = AccountLedger::where('name', 'EXCISE DUTY 80%')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Cr',
                        'amount'         => $excise_80,
                        'line_narration' => 'Excise Duty 80% - ' . $request->bill_no,
                    ]);
                }
            }

            // Excise Duty 20%
            if ($excise_20 > 0) {
                $l = AccountLedger::where('name', 'EXCISE DUTY 20%')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Dr',
                        'amount'         => $excise_20,
                        'line_narration' => 'Excise Duty 20% - ' . $request->bill_no,
                    ]);
                }
            }

            if ($case_purchase_amt > 0) {

                $ledger = AccountLedger::where('name', 'CASH PURCHASE')->first();

                if ($ledger) {

                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $ledger->id,
                        'dc'             => 'Cr',
                        'amount'         => $case_purchase_amt,
                        'line_narration' => 'Cash Purchase Adjustment - ' . $request->bill_no,
                    ]);
                }
            }
            // if ($rsgsm > 0) {
            //     $l = AccountLedger::where('name', 'RSGSM Purchase')->first();
            //     if ($l) {
            //         VoucherLine::create([
            //             'voucher_id'     => $voucher->id,
            //             'ledger_id'      => $l->id,
            //             'dc'             => 'Dr',
            //             'amount'         => $rsgsm,
            //             'line_narration' => 'RSGSM Purchase - ' . $request->bill_no,
            //         ]);
            //     }
            // }

            if ($permit > 0) {
                $l = AccountLedger::where('name', 'Permit Fee')->first();
                if ($l) {
                    VoucherLine::create([
                        'voucher_id'     => $voucher->id,
                        'ledger_id'      => $l->id,
                        'dc'             => 'Cr',
                        'amount'         => $permit,
                        'line_narration' => 'Permit Fee - ' . $request->bill_no,
                    ]);
                }
            }

            // if ($gFull > 0) {
            //     $l = AccountLedger::where('name', 'Guarantee Fulfilled')->first();
            //     if ($l) {
            //         VoucherLine::create([
            //             'voucher_id'     => $voucher->id,
            //             'ledger_id'      => $l->id,
            //             'dc'             => 'Dr',
            //             'amount'         => $gFull,
            //             'line_narration' => 'Guarantee Fulfilled - ' . $request->bill_no,
            //         ]);
            //     }
            // }

            // 3.5 Cr Vendor (full bill amount)
            VoucherLine::create([
                'voucher_id'     => $voucher->id,
                'ledger_id'      => $vendorLedgerId,
                'dc'             => 'Cr',
                'amount'         => $grandAmount,
                'line_narration' => 'Vendor: ' . $vendor->name,
            ]);

            DB::commit();

            return redirect()
                ->route('purchase.list')
                ->with('success', 'Delivery has been successfully added.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to create purchase order.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        // Load main purchase + its items + related products
        $purchase = Purchase::with(['purchaseProducts'])->findOrFail($id);

        $purchaseProducts = $purchase->purchaseProducts;

        // SAME DATA AS create()
        $vendors = VendorList::where('is_active', 1)->get();
        $products = Product::select('id', 'name')->where('is_deleted', 'no')->get();
        $expMainCategory = ExpenseCategory::where('expense_type_id', 1)->get();
        $purchaseLedger = PurchaseLedger::where('is_active', 'Yes')->get();
        $subcategories = DB::table('sub_categories')->where('is_deleted', 'no')->get();

        $purchaseGroupNames = ['Purchase Ledger', 'Purchase Ledgers', 'Purchase Accounts'];
        $vendorGroupNames = ['Sundry Creditors'];

        $ledgers = DB::table('account_ledgers as l')
            ->join('account_groups as g', 'g.id', '=', 'l.group_id')
            ->whereIn('g.name', $purchaseGroupNames)
            ->where(function ($q) {
                $q->where('l.is_deleted', 'No')->orWhereNull('l.is_deleted');
            })
            ->where(function ($q) {
                $q->where('l.is_active', 1)->orWhere('l.is_active', 'Yes');
            })
            ->orderBy('l.name')
            ->get(['l.id', 'l.name']);

        $ledgersAll = DB::table('account_ledgers as l')
            ->join('account_groups as g', 'g.id', '=', 'l.group_id')
            ->whereIn('g.name', $vendorGroupNames)
            ->where(function ($q) {
                $q->where('l.is_deleted', 'No')->orWhereNull('l.is_deleted');
            })
            ->where(function ($q) {
                $q->where('l.is_active', 1)->orWhere('l.is_active', 'Yes');
            })
            ->orderBy('l.name')
            ->get(['l.id', 'l.name']);

        return view('purchase.edit', compact(
            'purchase',
            'subcategories',
            'vendors',
            'products',
            'expMainCategory',
            'purchaseLedger',
            'ledgers',
            'ledgersAll',
            'purchaseProducts'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vendor_id' => 'required',
            'vendor_new_id' => 'required',
            'bill_no' => ['required', 'string', 'max:255', Rule::unique('purchases', 'bill_no')->ignore($id)],
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

        DB::beginTransaction();

        try {

            $purchase = Purchase::with('items')->findOrFail($id);
            $inventoryService = new \App\Services\InventoryService();

            $store_id = 1;
            $purchaseDate = $request->date;

            $purchaseShift = ShiftClosing::where('branch_id', $store_id)
                ->whereDate('created_at', $purchaseDate)
                ->first();

            $shift_id = $purchaseShift->id ?? null;

            /*
        =====================================================
        1. INVENTORY DIFFERENCE LOGIC
        =====================================================
        */

            $oldItems = [];
            foreach ($purchase->items as $item) {
                $oldItems[$item->product_id] = $item;
            }

            $newProductIds = collect($request->products)->pluck('product_id')->toArray();
            $affectedProducts = [];

            foreach ($request->products as $product) {

                $product_id = $product['product_id'];
                $batch = $product['batch'];
                $newQty = (float) $product['qnt'];

                $oldQty = isset($oldItems[$product_id])
                    ? (float) $oldItems[$product_id]->qnt
                    : 0;

                $difference = $newQty - $oldQty;

                if ($difference == 0) {
                    continue;
                }

                $expiryDate = Carbon::parse($product['mfg_date'])->addYear();

                $inventory = Inventory::firstOrCreate([
                    'product_id' => $product_id,
                    'store_id' => $store_id,
                    'location_id' => 1,
                    'batch_no' => $batch,
                ], [
                    'expiry_date' => $expiryDate,
                    'quantity' => 0,
                    'added_by' => Auth::id(),
                ]);

                if ($difference > 0) {

                    $inventory->increment('quantity', $difference);

                    stockStatusChange(
                        $product_id,
                        $store_id,
                        $difference,
                        'add_stock',
                        $shift_id,
                        '',
                        $purchaseDate
                    );

                    $inventoryService->transferProduct(
                        $product_id,
                        $inventory->id,
                        $store_id,
                        '',
                        $difference,
                        'add_stock'
                    );
                } else {

                    $removeQty = abs($difference);

                    $inventory->decrement('quantity', $removeQty);

                    stockStatusChange(
                        $product_id,
                        $store_id,
                        $removeQty,
                        'remove_stock',
                        $shift_id,
                        '',
                        $purchaseDate
                    );

                    $inventoryService->transferProduct(
                        $product_id,
                        $inventory->id,
                        $store_id,
                        '',
                        $removeQty,
                        'remove_stock'
                    );
                }

                $affectedProducts[] = $product_id;
            }

            /*
        =====================================================
        2. HANDLE REMOVED PRODUCTS
        =====================================================
        */

            foreach ($purchase->items as $oldItem) {

                if (!in_array($oldItem->product_id, $newProductIds)) {

                    $inventory = Inventory::where('product_id', $oldItem->product_id)
                        ->where('store_id', $store_id)
                        ->where('location_id', 1)
                        ->where('batch_no', $oldItem->batch)
                        ->first();

                    if ($inventory) {

                        $removeQty = (float) $oldItem->qnt;
                        $inventory->decrement('quantity', $removeQty);

                        stockStatusChange(
                            $oldItem->product_id,
                            $store_id,
                            $removeQty,
                            'remove_stock',
                            $shift_id,
                            '',
                            $purchaseDate
                        );

                        $inventoryService->transferProduct(
                            $oldItem->product_id,
                            $inventory->id,
                            $store_id,
                            '',
                            $removeQty,
                            'remove_stock'
                        );

                        $affectedProducts[] = $oldItem->product_id;
                    }
                }
            }

            /*
        =====================================================
        3. DELETE OLD ITEMS
        =====================================================
        */

            $purchase->items()->delete();

            /*
        =====================================================
        4. INSERT NEW ITEMS
        =====================================================
        */

            $subTotal = 0;

            foreach ($request->products as $product) {

                $subTotal += (float) $product['amount'];

                PurchaseProduct::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product['product_id'],
                    'brand_name' => $product['brand_name'],
                    'batch' => $product['batch'],
                    'mfg_date' => $product['mfg_date'],
                    'mrp' => $product['mrp'],
                    'qnt' => $product['qnt'],
                    'rate' => $product['rate'],
                    'amount' => $product['amount'],
                ]);
            }

            /*
        =====================================================
        5. UPDATE PURCHASE MASTER
        =====================================================
        */

            $purchase->update([
                'bill_no' => $request->bill_no,
                'vendor_id' => $request->vendor_id,
                'vendor_new_id' => $request->vendor_new_id,
                'parchase_ledger' => $request->parchase_ledger,
                'date' => $purchaseDate,
                'total' => $request->total,
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
                'loading_charges' => $request->loading_charges ?? 0,
                'itp_value' => $request->itp_value,
                'updated_by' => Auth::id(),
            ]);

            /*
        =====================================================
        6. DELETE OLD VOUCHER
        =====================================================
        */

            $oldVoucher = Voucher::where('gen_id', $purchase->id)
                ->where('voucher_type', 'Purchase')
                ->first();

            if ($oldVoucher) {
                VoucherLine::where('voucher_id', $oldVoucher->id)->delete();
                $oldVoucher->delete();
            }

            /*
        =====================================================
        7. CREATE NEW VOUCHER (SAME AS STORE)
        =====================================================
        */

            $vendor = VendorList::findOrFail($request->vendor_id);

            $baseAmount  = (float) $request->total;
            $grandAmount = (float) $request->total_amount;

            $voucher = Voucher::create([
                'gen_id' => $purchase->id,
                'voucher_date' => $request->date,
                'voucher_type' => 'Purchase',
                'ref_no' => $request->bill_no,
                'branch_id' => $store_id,
                'narration' => 'Purchase bill no ' . $request->bill_no,
                'created_by' => Auth::id(),
                'party_ledger_id' => $request->parchase_ledger,
                'sub_total' => $baseAmount,
                'discount' => 0,
                'tax' => ($request->vat ?? 0) + ($request->surcharge_on_vat ?? 0),
                'grand_total' => $grandAmount,
            ]);

            /*
        ======================
        DR PURCHASE
        ======================
        */

            VoucherLine::create([
                'voucher_id' => $voucher->id,
                'ledger_id' => $request->parchase_ledger,
                'dc' => 'Dr',
                'amount' => $baseAmount,
                'line_narration' => 'Purchase - ' . $request->bill_no,
            ]);

            /*
        ======================
        DR TAX LEDGERS
        ======================
        */

            $taxLedgers = [
                'AED TO BE PAID' => $request->aed_to_be_paid,
                'TCS' => $request->tcs,
                'VAT' => $request->vat,
                'SURCHARGE ON VAT' => $request->surcharge_on_vat,
                'BLF' => $request->blf,
                'EXCISE FEE' => $request->excise_fee,
                'Loading Charges' => $request->loading_charges,
                'COMPOSITION VAT' => $request->composition_vat,
                'SURCHARGE ON CA' => $request->surcharge_on_ca,
                'Permit Fee' => $request->permit_fee
            ];

            foreach ($taxLedgers as $ledgerName => $amount) {

                if ($amount > 0) {

                    $ledger = AccountLedger::where('name', $ledgerName)->first();

                    if ($ledger) {

                        VoucherLine::create([
                            'voucher_id' => $voucher->id,
                            'ledger_id' => $ledger->id,
                            'dc' => 'Dr',
                            'amount' => $amount,
                            'line_narration' => $ledgerName . ' - ' . $request->bill_no,
                        ]);
                    }
                }
            }

            /*
        ======================
        CR VENDOR
        ======================
        */

            VoucherLine::create([
                'voucher_id' => $voucher->id,
                'ledger_id' => $request->vendor_new_id,
                'dc' => 'Cr',
                'amount' => $grandAmount,
                'line_narration' => 'Vendor: ' . $vendor->name,
            ]);

            /*
        =====================================================
        8. RECALCULATE STOCK
        =====================================================
        */

            foreach (array_unique($affectedProducts) as $pid) {
                recalculateStockFromDate($pid, $store_id, $purchaseDate);
            }

            DB::commit();

            return redirect()
                ->route('purchase.list')
                ->with('success', 'Purchase updated successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Failed to update purchase: ' . $e->getMessage()])
                ->withInput();
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
                'total' => '₹' . rtrim(rtrim($purchase->total, '0'), '.'),
                'total_amount' => '₹' . rtrim(rtrim($purchase->total_amount, '0'), '.'),
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

    public function getLedgersByVendor($vendorId)
    {
        // Vendor 3 → Only Sundry Creditors
        if ($vendorId == 3) {
            $ledgers = AccountLedger::where('name', 'Sundry Creditors')->get();

            return response()->json($ledgers);
        }

        // Get vendor
        $vendor = \App\Models\VendorList::find($vendorId);

        if (!$vendor) {
            return response()->json([]);
        }

        // Vendor 1 & 2 → match by name BUT exclude Sundry Creditors
        $ledgers = AccountLedger::where('name', 'LIKE', '%' . $vendor->name . '%')
            ->where('name', '!=', 'Sundry Creditors')
            ->get();

        return response()->json($ledgers);
    }

    public function getProductByBarcode(string $barcode)
    {
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
            ->where('products.barcode', $barcode) // 🔥 Changed Here
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

        return response()->json($record);
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

    public function productsBySubcategory($id)
    {
        // Optional: validate id exists
        if (! SubCategory::where('id', $id)->exists()) {
            return response()->json([], 404);
        }

        // Fetch products belonging to this subcategory (adjust column names to your schema)
        $products = Product::where('subcategory_id', $id)
            ->select('id', 'name', 'mrp', 'cost_price', 'sell_price') // only bring what's needed
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }
}
