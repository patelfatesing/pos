<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use PDF;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Commissionuser;
use App\Models\Partyuser;
use App\Models\Product;
use App\Models\PartyCustomerProductsPrice;
use App\Models\InvoiceActivityLog;
use App\Models\ShiftClosing;
use App\Models\DailyProductStock;
use App\Models\InvoiceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use App\Models\Accounting\AccountLedger;
use App\Models\Accounting\Voucher;

class InvoiceController extends Controller
{

    public function show(Invoice $invoice)
    {
        $commissionUser = Commissionuser::where('id', $invoice->commission_user_id)
            ->where('status', 'Active')
            ->first();
        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();

        return view('invoice.view', compact('invoice', 'commissionUser', 'partyUser'));
    }

    public function download(Invoice $invoice)
    {
        $commissionUser = Commissionuser::where('id', $invoice->commission_user_id)
            ->where('status', 'Active')
            ->first();

        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();

        $customer_name = 'N/A';

        if ($partyUser && !empty(trim($partyUser->first_name))) {
            $customer_name = $partyUser->first_name;
        } elseif ($commissionUser && !empty(trim($commissionUser->first_name))) {
            $customer_name = $commissionUser->first_name;
        }

        $pdf = PDF::loadView('invoice', [
            'invoice' => $invoice,
            'invoice_number' => $invoice->invoice_number,
            'cartitems' => collect($invoice->items),
            'items' => collect($invoice->items),
            'sub_total' => $invoice->sub_total,
            'tax' => $invoice->tax,
            'commissionAmount' => $invoice->commission_amount,
            'partyAmount' => $invoice->party_amount,
            'total' => $invoice->total,
            'commissionUser' => $commissionUser,
            'partyUser' => $partyUser,
            'customer_name' => $customer_name,
            'created_at' => $invoice->created_at,
        ]);
        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    public function viewInvoice(Invoice $invoice, $shift_id = '')
    {
        $commissionUser = Commissionuser::where('status', 'Active')->find($invoice->commission_user_id);

        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();
        return view('invoice.viewInvoice', compact('invoice', 'commissionUser', 'partyUser', 'shift_id'));
    }

    public function invoiceModal($id, $shift_id = '')
    {
        $invoice = Invoice::findOrFail($id);
        $commissionUser = Commissionuser::where('status', 'Active')->find($invoice->commission_user_id);

        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();
        return view('invoice.viewInvoiceModal', compact('invoice', 'commissionUser', 'partyUser', 'shift_id'));
    }

    public function editSales($id)
    {
        $verify = request('verify');

        $invoice = Invoice::with(['partyUser', 'commissionUser'])->find($id);

        $allProducts = Product::select(
            'id',
            'name',
            'mrp',
            'discount_price',
            'sell_price',
            'category_id',
            'subcategory_id'
        )
            ->where('is_deleted', 'no')
            ->with(['category', 'subcategory'])
            ->get();

        $commissionUser = Commissionuser::where('status', 'Active')->find($invoice->commission_user_id);

        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();

        // Load party-specific prices only if needed
        $partyPrices = collect();
        if ($invoice->branch_id == 1 && $invoice->party_user_id) {
            $partyPrices = PartyCustomerProductsPrice::where('party_user_id', $invoice->party_user_id)
                ->whereIn('product_id', $allProducts->pluck('id'))
                ->get();
        }

        $branch_data = Branch::find($invoice->branch_id);
        $type = request('type');

        return view('invoice.editSales', compact('type', 'verify', 'invoice', 'commissionUser', 'partyUser', 'allProducts', 'partyPrices', 'branch_data'));
    }

    public function addSales($branchId, $shift_id)
    {

        $allProducts = Product::select(
            'id',
            'name',
            'mrp',
            'discount_price',
            'sell_price',
            'category_id',
            'subcategory_id'
        )
            ->where('is_deleted', 'no')
            ->with(['category', 'subcategory'])
            ->get();

        $Shift_data = ShiftClosing::find($shift_id);
        $branch_data = Branch::find($branchId);

        $partyUsers = Partyuser::where('status', 'Active')
            ->get();

        $commissionUsers = Commissionuser::where('is_active', '1')
            ->get();

        $type = request('type');

        return view('invoice.addSales', compact('branch_data', 'Shift_data', 'partyUsers', 'allProducts', 'commissionUsers', 'type'));
    }

    public function viewHoldInvoice(Invoice $invoice, $shift_id)
    {
        $commissionUser = Commissionuser::where('status', 'Active')->find($invoice->commission_user_id);

        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();
        return view('invoice.viewHoldInvoice', compact('invoice', 'commissionUser', 'partyUser', 'shift_id'));
    }

    public function addItem(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $items = $invoice->items ?? [];

        $product = Product::findOrFail($request->product_id);

        foreach ($items as &$item) {
            if ($item['product_id'] == $product->id) {
                $item['quantity'] += $request->quantity;
                $invoice->items = $items;
                $invoice->save();
                return response()->json(['success' => true]);
            }
        }

        $items[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => $request->quantity,
            'mrp' => $product->mrp
        ];

        $invoice->items = $items;
        $invoice->save();

        return response()->json(['success' => true]);
    }

    public function updateQty(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $items = $invoice->items ?? [];

        foreach ($items as &$item) {
            if ($item['product_id'] == $request->product_id) {
                $item['quantity'] = $request->quantity;
                break;
            }
        }

        $invoice->items = $items;
        $invoice->save();

        return response()->json(['success' => true]);
    }

    public function deleteItem(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $items = collect($invoice->items ?? [])->reject(fn($item) => $item['product_id'] == $request->product_id)->values();
        $invoice->items = $items;
        $invoice->save();

        return response()->json(['success' => true]);
    }

    public function updateItems(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        // dd($request->all());

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.mrp' => 'required|numeric|min:0',
            'items.*.price' => 'nullable',
            'items.*.category' => 'nullable|string',
            'items.*.subcategory' => 'nullable|string',
            'items.*.discount_price' => 'nullable|string',
            'creditpay' => 'nullable|numeric|min:0',
        ]);

        $currentItems = collect($invoice->items);
        $newItems = collect($validated['items']);

        $branchId = $invoice->branch_id;
        $invoiceDate = $invoice->created_at->toDateString();

        $invoiceShift = ShiftClosing::where('branch_id', $branchId)
            ->where('start_time', '<=', $invoice->created_at)
            ->where('end_time', '>=', $invoice->created_at)
            ->first();

        $invoiceShiftId = optional($invoiceShift)->id;

        $affectedProducts = [];
        $subTotal = 0;
        $totalQty = 0;
        $total = 0;

        foreach ($newItems as $item) {

            $productId = $item['product_id'];
            $newQty = (int) $item['quantity'];

            $affectedProducts[] = $productId;

            $newMRP = (float) $item['mrp'];
            $sell_price = (float) $item['sell_price'];

            $totalQty += $newQty;
            $subTotal += $newQty * $newMRP;
            $total += $newQty * $sell_price;

            $old = $currentItems->firstWhere('product_id', $productId);
            $oldQty = $old ? (int) $old['quantity'] : 0;

            // ================= NEW PRODUCT =================
            if (!$old) {

                updateInventoryStock($productId, $branchId, $newQty, 'sale');

                if ($invoiceShiftId) {
                    stockStatusChangeNew($productId, $branchId, $newQty, 'sold_stock', $invoiceShiftId);
                }

                // ✅ LOG
                InvoiceActivityLog::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'item_added',
                    'description' => "Added product ID {$productId} qty {$newQty}",
                    'old_data' => null,
                    'new_data' => $item,
                    'user_id' => auth()->id(),
                ]);

                continue;
            }

            // ================= QTY INCREASE =================
            if ($newQty > $oldQty) {

                $diff = $newQty - $oldQty;

                updateInventoryStock($productId, $branchId, $diff, 'sale');

                if ($invoiceShiftId) {
                    stockStatusChangeNew($productId, $branchId, $diff, 'sold_stock', $invoiceShiftId);
                }

                // ✅ LOG
                InvoiceActivityLog::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'qty_increased',
                    'description' => "Product {$productId} qty increased {$oldQty} → {$newQty}",
                    'old_data' => ['quantity' => $oldQty],
                    'new_data' => ['quantity' => $newQty],
                    'user_id' => auth()->id(),
                ]);
            }

            // ================= QTY DECREASE =================
            elseif ($newQty < $oldQty) {

                $diff = $oldQty - $newQty;

                updateInventoryStock($productId, $branchId, $diff, 'refund');

                if ($invoiceShiftId) {
                    stockStatusChangeNew($productId, $branchId, $diff, 'refunded_order', $invoiceShiftId);
                }

                // ✅ LOG
                InvoiceActivityLog::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'qty_decreased',
                    'description' => "Product {$productId} qty decreased {$oldQty} → {$newQty}",
                    'old_data' => ['quantity' => $oldQty],
                    'new_data' => ['quantity' => $newQty],
                    'user_id' => auth()->id(),
                ]);
            }
        }

        // ================= REMOVED ITEMS =================
        $currentItems->each(function ($item) use ($newItems, $branchId, $invoiceShiftId, $invoice) {

            if (!$newItems->contains('product_id', $item['product_id'])) {

                updateInventoryStock($item['product_id'], $branchId, $item['quantity'], 'refund');

                if ($invoiceShiftId) {
                    stockStatusChange($item['product_id'], $branchId, $item['quantity'], 'refunded_order', $invoiceShiftId);
                }

                // ✅ LOG
                InvoiceActivityLog::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'item_removed',
                    'description' => "Removed product ID {$item['product_id']} qty {$item['quantity']}",
                    'old_data' => $item,
                    'new_data' => null,
                    'user_id' => auth()->id(),
                ]);
            }
        });

        foreach (array_unique($affectedProducts) as $pid) {
            recalculateStockFromDate($pid, $branchId, $invoiceDate);
        }

        // ================= CREDIT LOG =================
        $oldCredit = $invoice->creditpay ?? 0;
        $newCredit = $request->creditpay ?? 0;

        if ($oldCredit != $newCredit && $branchId == 1) {

            InvoiceActivityLog::create([
                'invoice_id' => $invoice->id,
                'action' => 'credit_change',
                'description' => "Credit changed {$oldCredit} → {$newCredit}",
                'old_data' => ['creditpay' => $oldCredit],
                'new_data' => ['creditpay' => $newCredit],
                'user_id' => auth()->id(),
            ]);
        }

        // ================= SAVE =================
        $invoice->items = $validated['items'];
        $invoice->creditpay = $newCredit;
        if ($branchId == 1) {
            $invoice->party_amount = $request->total_discount ?? 0;
        } else {
            $invoice->commission_amount = $request->total_discount ?? 0;
        }

        if ($request->payment_method == 'cashupi') {
            $invoice->cash_amount = $request->cash_amount;
            $invoice->upi_amount = $request->upi_amount;
            $invoice->online_amount = 0;
        } elseif ($request->payment_method == 'cash') {
            $invoice->cash_amount = $request->cash_amount;
            $invoice->upi_amount = 0;
            $invoice->online_amount = 0;
        } elseif ($request->payment_method == 'online') {
            $invoice->cash_amount = 0;
            $invoice->upi_amount = $request->upi_amount;
            $invoice->online_amount = 0;
        } elseif ($request->payment_method == 'credit') {
            $invoice->cash_amount = 0;
            $invoice->upi_amount = 0;
            $invoice->online_amount = 0;
            $invoice->creditpay =$newCredit;
        }

        $invoice->total_item_total = $subTotal;
        $invoice->total_item_qty = $totalQty;
        $invoice->total = $request->total;
        $invoice->edit_in = 'yes';
         $invoice->payment_mode = $request->payment_method;
        $invoice->save();

        if ($request->type == 'admin_sale') {
            return redirect()->route('sales.salas-report')->with('success', 'Invoice items updated successfully.');
        } else {

            return redirect()->route('shift-manage.view', [
                'id' => $invoice->branch_id,
                'shift_id' => $invoice->shift_id
            ])->with('success', 'Invoice updated with logs ✅');
        }
    }

    public function fetchHistory($id)
    {
        $invoice = Invoice::findOrFail($id);
        $logs = $invoice->activityLogs()->with('user')->latest()->get();

        return view('invoice.invoiceHistory', compact('logs'));
    }

    public function getPartyCustomerDiscount($partyUserId)
    {
        // Fetch the discount for the party customer, assuming the table contains `party_user_id` and `discount`
        $discount = PartyCustomerProductsPrice::where('party_user_id', $partyUserId)
            ->first(); // You can adjust the logic if needed

        // If a discount is found, return it; otherwise, return a default value (e.g., 0)
        return response()->json([
            'discount' => $discount ? $discount->cust_discount_price : 0
        ]);
    }

    public function InsertSale(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.mrp' => 'required|numeric|min:0',
            'items.*.discount_price' => 'nullable|string',
            'creditpay' => 'nullable|numeric|min:0',
        ]);

        try {

            DB::beginTransaction();

            // Get the branch id
            $branch_id = $request->branch_id;
            $commissionAmount = 0;
            $partyAmount = 0;
            $shift_id = $request->shift_id;
            $Shift_data = ShiftClosing::find($shift_id);
            if ($branch_id == 1) {
                $party_user_id = $request->party_user_id;
                $partyUser = PartyUser::where('status', 'Active')->where('is_delete', 'No')->find($party_user_id);

                $partyAmount = $request->total_discount;
                // if (!empty($partyUser) && $request->creditpay) {
                //     $partyUser->use_credit = (int)$partyUser->use_credit + (int)$request->creditpay;
                //     $partyUser->left_credit = (int)$partyUser->credit_points - (int)$partyUser->use_credit;
                //     $partyUser->save();
                // }
            } else {
                $commissionAmount = $request->total_discount;
                if (!empty($request->commission_user_id)) {
                    $commission_user_id = $request->commission_user_id;
                    $commissionUser = CommissionUser::where('is_active', '1')->find($commission_user_id);
                } else {
                    $commissionUser = null;
                }
            }

            // Update party user's credit details if applicable

            $cartitems = collect($request->items);
            $roundedTotal = 0;
            $paymentType = $request->payment_method;

            $online_amount = 0;
            $upi = 0;
            $cash = 0;

            if ($paymentType == 'online') {
                $online_amount = $request->upi_amount ?? 0;
            } elseif ($paymentType == 'cashupi') {
                $upi =  $request->upi_amount ?? 0;
                $cash = $request->cash_amount ?? 0;
            } else {
                $cash = $request->cash_amount ?? 0;
            }

            $cashPayChangeAmt = 0;
            $creditPay = $request->creditpay ?? 0;
            $sub_total = $request->sub_total ?? 0;

            $invoice_status = ($request->creditPay == 0) ? "paid" : "unpaid";

            $cashAmount = $request->total;
            // $cartitems = $request->items;

            // Handle low stock notifications
            $arr_low_stock = [];
            $affectedProducts = []; // 🔴 ADD

            foreach ($cartitems as $productId => $item) {
                $affectedProducts[] = $item['product_id']; // 🔴 ADD
                $product = Product::with('inventorieUnfiltered')  // Load the inventories relationship with the product
                    ->where('id', $item['product_id'])  // Filter by product ID
                    ->whereHas('inventorieUnfiltered', function ($query) use ($branch_id) {  // Filter inventories by store_id (if needed)
                        $query->where('store_id', $branch_id);  // Filter inventories by store ID
                    })
                    ->first();

                $inventories = $product->inventorieUnfiltered;

                $totalQuantityNew = $inventories->sum('quantity') - $item['quantity'];
                // $inventory = $product->inventorie;

                // DailyProductStock::where([
                //     'product_id' => $item['product_id'],
                //     'branch_id' => $branch_id,
                //     'shift_id' => $shift_id,
                //     'date' => $Shift_data->statrt_time,
                // ])->increment('modify_sale_remove_qty', $item['quantity']);

                // $currentShift = ShiftClosing::where('branch_id', $branch_id)
                //     ->where('start_time', '<=', now())
                //     ->where('end_time', '>=', now())
                //     ->first();

                stockStatusChange(
                    $item['product_id'],
                    $branch_id,
                    $item['quantity'],
                    'sold_stock',
                    $shift_id, // ✅ ONLY invoice shift
                    '',
                    $Shift_data->start_time
                );
                // if (!empty($currentShift)) {

                // stockStatusChange($item['product_id'], $branch_id, $item['quantity'], 'add_modify_stock', $currentShift->id);
                // DailyProductStock::where([
                //     'product_id' => $item['product_id'],
                //     'branch_id' => $branch_id,
                //     'shift_id' => $currentShift->id,
                //     'date' => $currentShift->start_time
                // ])->increment('modify_sale_remove_qty', $item['quantity']);
                // }

                \Log::info('Stock Status Changed for Product ID: ' . $item['product_id'] . ' Branch ID: ' . $branch_id . ' Quantity: ' . $item['quantity']);

                // Deduct from inventories based on available stock
                // if (isset($inventories) && $inventories->quantity >= $item['quantity']) {
                $inventories->quantity -= $item['quantity'];
                $inventories->save();
                // } else {
                // foreach ($inventories as $inventory) {
                //     if ($item <= 0) {
                //         break;
                //     }
                //     if ($inventory->quantity > 0) {
                //         $deductQty = min($item, $inventory->quantity);
                //         $inventory->quantity -= $deductQty;
                //         $inventory->save();
                //         $item -= $deductQty;
                //     }
                // }
                // }
            }

            // 🔴 RECALCULATE STOCK (IMPORTANT)
            foreach (array_unique($affectedProducts) as $pid) {
                recalculateStockFromDate(
                    $pid,
                    $branch_id,
                    Carbon::parse($Shift_data->start_time)->toDateString()
                );
            }

            // Create or update invoice
            $invoice_number = Invoice::generateInvoiceNumberNew($branch_id, $Shift_data->start_time);
            $resumedInvoice = Invoice::where('user_id', Auth::id())
                ->where('branch_id', $branch_id)
                ->where('status', 'Resumed')
                ->first();

            $invoice_number_to_use = $resumedInvoice->invoice_number ?? $invoice_number;
            $totalQuantity = $cartitems->sum(function ($items) {
                return $items['quantity'];  // Access 'quantity' from array
            });

            // Calculate total item total (sum of sell price * quantity for each item)
            $total_item_total = $cartitems->sum(function ($itemss) {
                return $itemss['sell_price'] * $itemss['quantity'];  // Multiply sell price with quantity
            });
            // Subtract 10 minutes from the end_time and format it as 'Y-m-d H:i:s'
            $modifiedEndTime = Carbon::parse($Shift_data->end_time)->subMinutes(10);

            // Format it in 'Y-m-d H:i:s' format for database insertion
            $created_at = $modifiedEndTime->format('Y-m-d H:i:s');

            $cust_name = '';
            if ($creditPay > 0 && $request->party_user_id != "") {

                $partyUser = PartyUser::where('status', 'Active')
                    ->where('is_delete', 'No')
                    ->where('id', $request->party_user_id) // use the foreign key
                    ->first();
                $cust_name = $partyUser->first_name ?? '';
                if (!empty($partyUser)) {

                    $partyUser->left_credit -= $creditPay;
                    $partyUser->use_credit += $creditPay;
                    $partyUser->save();
                }
            }


            if ($request->commission_user_id != "") {
                $commissionUser = Commissionuser::where('status', 'Active')
                    ->where('is_deleted', 'No')
                    ->where('id', $request->commission_user_id) // use the foreign key
                    ->first();

                $cust_name = $commissionUser->first_name ?? '';
            }

            \Log::info('Before Invoice Creation');
            $invoiceId = DB::table('invoices')->insertGetId([
                'user_id'            => Auth::id(),
                'branch_id'          => $branch_id,
                'roundof'            => $roundedTotal,
                'invoice_number'     => $invoice_number,
                'commission_user_id' => $commissionUser->id ?? null,
                'party_user_id'      => $partyUser->id ?? null,
                'payment_mode'       => $paymentType,
                'items'              => json_encode($cartitems),
                'total_item_qty'     => $totalQuantity,
                'total_item_total'   => $total_item_total,
                'upi_amount'         => $upi,
                'change_amount'      => $cashPayChangeAmt,
                'creditpay'          => $creditPay,
                'cash_amount'        => $cash,
                'online_amount'      => $online_amount,
                'sub_total'          => $sub_total,
                'status'             => 'Paid',
                'invoice_status'     => $invoice_status,
                'commission_amount'  => $commissionAmount,
                'party_amount'       => $partyAmount,
                'total'              => $cashAmount,
                'tax'                => 0,
                'sales_type'         => 'admin_sale',
                'created_at'         => $created_at,
                'updated_at'         => $created_at,
                'shift_id'           => $request->shift_id
            ]);

            // ================= POS VOUCHER BUILD =================

            $branchId = $branch_id;

            // 0) Init
            $lines              = [];
            $cashLedgerId       = null;
            $customer_ledger_id = null;

            // ============= 1) Tenders (Cash + UPI) =============

            $cashPaid = round((float) $cash, 2);
            $upiPaid  = round((float) $upi, 2);

            // CASH
            if ($cashPaid > 0) {
                $cashLedger = AccountLedger::where('name', 'CASH')->firstOrFail();
                $cashLedgerId = $cashLedger->id;

                $lines[] = [
                    'ledger_id'      => (int) $cashLedgerId,
                    'dc'             => 'Dr',
                    'amount'         => $cashPaid,
                    'line_narration' => 'Cash received',
                ];
            }

            // UPI
            if ($upiPaid > 0) {
                $branchData = Branch::where('branches.id', $branchId)
                    ->leftJoin('account_ledgers', 'branches.bank_ledger_id', '=', 'account_ledgers.id')
                    ->select('account_ledgers.name as bank_ledger_name')
                    ->first();

                $upiLedger = AccountLedger::where('name', $branchData->bank_ledger_name)->firstOrFail();

                $lines[] = [
                    'ledger_id'      => (int) $upiLedger->id,
                    'dc'             => 'Dr',
                    'amount'         => $upiPaid,
                    'line_narration' => 'UPI received',
                ];
            }

            // CREDIT (only branch 1)
            $creditUsed = ($branchId == 1) ? round((float) $creditPay, 2) : 0;

            // ============= 2) CUSTOMER LEDGER =============
            if (!empty($partyUser)) {
                $customerLedger = AccountLedger::where('name', $partyUser->first_name)->first();
                if ($customerLedger) {
                    $party_customer_ledger_id = $customerLedger->id;
                } else {

                    $customerLedger = AccountLedger::create([
                        'name'        => $partyUser->first_name,
                        'group_name'  => 'Sundry Debtors', // IMPORTANT for Tally
                        'group_id' => 19,
                        'opening_balance' => 0,
                        'debit_credit'    => 'Dr',
                        'created_by'      => auth()->id(),
                    ]);

                    $party_customer_ledger_id = $customerLedger->id;
                    \Log::info('Auto-created Party Ledger: ' . $customerLedger->name);
                }
            }

            // Credit Dr
            if ($branchId == 1 && $creditUsed > 0 && $party_customer_ledger_id) {
                $lines[] = [
                    'ledger_id'      => (int) $party_customer_ledger_id,
                    'dc'             => 'Dr',
                    'amount'         => $creditUsed,
                    'line_narration' => 'Credit to customer',
                ];
            }

            // Total Tender
            $totalTender = $cashPaid + $upiPaid + $creditUsed;

            // ============= 3) DISCOUNT =============
            $discountAmt = ($branchId == 1)
                ? round((float) $partyAmount, 2)
                : round((float) $commissionAmount, 2);

            if ($discountAmt > 0) {
                $discountLedger = AccountLedger::where('name', 'Discount Allowed')->firstOrFail();

                $lines[] = [
                    'ledger_id'      => (int) $discountLedger->id,
                    'dc'             => 'Dr',
                    'amount'         => $discountAmt,
                    'line_narration' => 'Discount allowed',
                ];
            }

            // ============= 4) SALES LEDGER =============
            $totalSale = round((float) $sub_total, 2);
            $netAmount = $totalSale - $discountAmt;

            if ($branchId == 1) {
                $salesLedger = AccountLedger::where('name', 'WAREHOUSE')->firstOrFail();
            } else {
                $branch = Branch::findOrFail($branchId);
                $salesLedger = AccountLedger::where('name', $branch->name)->firstOrFail();
            }
            $sales_ledger_id = $salesLedger->id;

            // ============= 5) ROUND OFF =============
            $roundOff = round($totalTender - $netAmount, 2);

            $roundLedger = AccountLedger::where('name', 'Round Off')->first();

            if ($roundOff < 0 && $roundLedger) {
                $lines[] = [
                    'ledger_id'      => (int) $roundLedger->id,
                    'dc'             => 'Dr',
                    'amount'         => abs($roundOff),
                    'line_narration' => 'Round Off (-)',
                ];
            }

            if ($roundOff > 0 && $roundLedger) {
                $lines[] = [
                    'ledger_id'      => (int) $roundLedger->id,
                    'dc'             => 'Cr',
                    'amount'         => $roundOff,
                    'line_narration' => 'Round Off (+)',
                ];
            }

            // ============= 6) SALES CR =============
            $lines[] = [
                'ledger_id'      => (int) $sales_ledger_id,
                'dc'             => 'Cr',
                'amount'         => $totalSale,
                'line_narration' => 'Sales: items',
            ];

            // ============= 7) BALANCE CHECK =============
            $dr = collect($lines)->where('dc', 'Dr')->sum('amount');
            $cr = collect($lines)->where('dc', 'Cr')->sum('amount');

            // echo "<pre>";
            // print_r($dr);
            // dd($cr);
            if (round($dr, 2) !== round($cr, 2)) {
                throw new \Exception("Voucher not balanced: Dr={$dr} Cr={$cr}");
            }

            // ============= 8) REF NO =============
            $prefix = "POS-" . $branchId . "-";

            $lastRef = Voucher::where('voucher_type', 'Sales')
                ->where('branch_id', $branchId)
                ->where('ref_no', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->value('ref_no');

            $nextNumber = $lastRef
                ? ((int) str_replace($prefix, '', $lastRef)) + 1
                : 1;

            $nextRef = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // ============= 9) MODE =============
            $mode = null;
            if ($cashPaid > 0 && $upiPaid > 0) {
                $mode = 'cash';
            } elseif ($cashPaid > 0) {
                $mode = 'cash';
            } elseif ($upiPaid > 0) {
                $mode = 'upi';
            }

            // ============= 10) PAYLOAD =============
            $payload = [
                'voucher_date'    => Carbon::now()->format('Y-m-d'),
                'voucher_type'    => 'Sales',
                'branch_id'       => $branchId,
                'ref_no'          => $nextRef,
                'narration'       => 'Counter sale',

                'party_ledger_id' => $customer_ledger_id,

                'mode'            => $mode,
                'cash_ledger_id'  => $cashLedgerId,

                'sub_total'       => $totalSale,
                'discount'        => $discountAmt,
                'tax'             => 0,
                'grand_total'     => $netAmount + max(0, $roundOff),

                'lines'           => $lines,
            ];

            // CREATE VOUCHER
            $voucher = $this->posTransaction($payload);

            // Link with invoice
            if ($voucher) {
                DB::table('vouchers')
                    ->where('id', $voucher->id)
                    ->update(['gen_id' => $invoiceId]);
            }

            $invoice = DB::table('invoices')
                ->where('id', $invoiceId)
                ->first();

            $pdf = App::make('dompdf.wrapper');

            $pdf->loadView('invoice', ['invoice' => $invoice, 'items' => $cartitems, 'branch' => auth()->user()->userinfo->branch, 'customer_name' => $cust_name, "ref_no" => '', "hold_date" => '']);
            $pdfPath = storage_path('app/public/invoices/' . $invoice_number . '.pdf');
            $pdf->save($pdfPath);

            // \Log::info('Invoice Created: ', $invoice->toArray());
            // InvoiceHistory::logFromInvoice($invoice, 'created', Auth::id());
            DB::commit();
            if ($request->type == 'admin_sale') {
                return redirect()->route('sales.salas-report')->with('success', 'Invoice items updated successfully.');
            } else {
                return redirect()->route('shift-manage.view', [
                    'id' => $branch_id, // or $branch_id or whatever your id is
                    'shift_id' => $request->shift_id
                ])->with('success', 'Invoice items updated successfully.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transaction failed when creating user and wallet', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function posTransaction(array $arr_data)
    {

        $nv = function ($v) {
            return ($v === '' || $v === null) ? null : $v;
        };

        $type = $nv($arr_data['voucher_type'] ?? null);
        $mode = $nv($arr_data['mode'] ?? null);

        // Party normalization
        $partyFromPR = $nv($arr_data['party_ledger_id'] ?? null) ?: $nv($arr_data['pr_party_ledger'] ?? null);
        $partyFromTR = $nv($arr_data['party_ledger_id'] ?? null) ?: $nv($arr_data['tr_party_ledger'] ?? null);

        if (in_array($type, ['Payment', 'Receipt'])) {
            $party = $partyFromPR ?: $partyFromTR;
        } elseif (in_array($type, ['Sales', 'Purchase', 'DebitNote', 'CreditNote'])) {
            $party = $partyFromTR ?: $partyFromPR;
        } else {
            $party = null;
        }

        // Mode: cash / bank / upi / card
        $cashLedger = $nv($arr_data['cash_ledger_id'] ?? null) ?: $nv($arr_data['pr_cash_ledger'] ?? null);
        $bankLedger = $nv($arr_data['bank_ledger_id'] ?? null) ?: $nv($arr_data['pr_bank_ledger'] ?? null);

        if ($mode === 'cash') {
            $bankLedger = null;
        } elseif (in_array($mode, ['bank', 'upi', 'card'])) {
            $cashLedger = null;
        } else {
            $cashLedger = $bankLedger = null;
        }

        // Totals
        $subTotal   = $nv($arr_data['sub_total'] ?? $arr_data['tr_subtotal'] ?? null);
        $discount   = $nv($arr_data['discount'] ?? $arr_data['tr_discount'] ?? null);
        $tax        = $nv($arr_data['tax'] ?? $arr_data['tr_tax'] ?? null);
        $grandTotal = $nv($arr_data['grand_total'] ?? $arr_data['tr_grand'] ?? null);

        if (!$grandTotal && ($subTotal || $discount || $tax)) {
            $grandTotal = round(($subTotal ?? 0) - ($discount ?? 0) + ($tax ?? 0), 2);
        }

        $fromLedger = $nv($arr_data['from_ledger_id'] ?? $arr_data['ct_from'] ?? null);
        $toLedger   = $nv($arr_data['to_ledger_id'] ?? $arr_data['ct_to'] ?? null);
        $branchId   = $nv($arr_data['branch_id'] ?? null);
        $refNo      = $nv($arr_data['ref_no'] ?? null);

        // Lines array
        $lines = $arr_data['lines'] ?? [];
        if (count($lines) < 2) {
            throw new \Exception('At least two lines (Dr/Cr) are required.');
        }

        // --- Check Debit/Credit Balance ---
        $dr = 0;
        $cr = 0;
        foreach ($lines as $line) {
            if (($line['dc'] ?? '') === 'Dr') {
                $dr += (float)($line['amount'] ?? 0);
            } elseif (($line['dc'] ?? '') === 'Cr') {
                $cr += (float)($line['amount'] ?? 0);
            }
        }

        if (round($dr, 2) !== round($cr, 2)) {
            // throw new \Exception('Debit and Credit total mismatch.');
        }

        try {
            return DB::transaction(function () use (
                $arr_data,
                $type,
                $party,
                $mode,
                $cashLedger,
                $bankLedger,
                $fromLedger,
                $toLedger,
                $branchId,
                $refNo,
                $subTotal,
                $discount,
                $tax,
                $grandTotal,
                $lines
            ) {
                // ... existing create code ...

                $voucher = \App\Models\Accounting\Voucher::create([
                    'voucher_date'    => $arr_data['voucher_date'] ?? now(),
                    'voucher_type'    => $type,
                    'ref_no'          => $refNo,
                    'branch_id'       => $branchId,
                    'narration'       => $arr_data['narration'] ?? null,
                    'created_by'      => 1,
                    'party_ledger_id' => $party,
                    'mode'            => $mode,
                    'instrument_no'   => $arr_data['instrument_no'] ?? null,
                    'instrument_date' => $arr_data['instrument_date'] ?? null,
                    'cash_ledger_id'  => $cashLedger,
                    'bank_ledger_id'  => $bankLedger,
                    'from_ledger_id'  => $fromLedger,
                    'to_ledger_id'    => $toLedger,
                    'sub_total'       => $subTotal ?? 0,
                    'discount'        => $discount ?? 0,
                    'tax'             => $tax ?? 0,
                    'grand_total'     => $grandTotal ?? 0,
                ]);


                foreach ($lines as $line) {
                    $voucher->lines()->create([
                        'ledger_id'      => $line['ledger_id'],
                        'dc'             => $line['dc'],
                        'amount'         => round((float)$line['amount'], 2),
                        'line_narration' => $line['line_narration'] ?? null,
                    ]);
                }
                // dd($voucher);
                return $voucher;
            });
        } catch (QueryException $qe) {
            // DB-level error: show SQL + bindings + error info
            Log::error('posTransaction QueryException', [
                'message' => $qe->getMessage(),
                'sql'     => $qe->sql ?? null,
                'bindings' => $qe->bindings ?? null,
                'errorInfo' => $qe->errorInfo ?? null,
                'payload' => $arr_data,
            ]);
            throw $qe; // rethrow so Livewire / caller gets exception (or return/handle)
        } catch (\Throwable $e) {
            Log::error('posTransaction Exception', [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $arr_data,
            ]);
            throw $e;
        }
    }

    public function editSales_old($id)
    {
        $invoice = Invoice::with(['partyUser', 'commissionUser'])->find($id);

        $allProducts = Product::select('id', 'name', 'mrp', 'discount_price', 'sell_price')->where('is_deleted', 'no')->get();

        $commissionUser = Commissionuser::where('status', 'Active')->find($invoice->commission_user_id);

        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();

        // Load party-specific prices only if needed
        $partyPrices = collect();
        if ($invoice->branch_id == 1 && $invoice->party_user_id) {
            $partyPrices = PartyCustomerProductsPrice::where('party_user_id', $invoice->party_user_id)
                ->whereIn('product_id', $allProducts->pluck('id'))
                ->get();
        }

        return view('invoice.editSales', compact('invoice', 'commissionUser', 'partyUser', 'allProducts', 'partyPrices'));
    }
}
