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

    public function editSales($id)
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

    public function addSales($branchId, $shift_id)
    {

        $allProducts = Product::select('id', 'name', 'mrp', 'discount_price', 'sell_price')->where('is_deleted', 'no')->get();

        $Shift_data = ShiftClosing::find($shift_id);
        $branch_data = Branch::find($branchId);

        $partyUsers = Partyuser::where('status', 'Active')
            ->get();

        $commissionUsers = Commissionuser::where('is_active', '1')
            ->get();

        return view('invoice.addSales', compact('branch_data', 'Shift_data', 'partyUsers', 'allProducts', 'commissionUsers'));
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

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.mrp' => 'required|numeric|min:0',
            'creditpay' => 'nullable|numeric|min:0',
        ]);

        $currentItems = collect($invoice->items);
        $newItems = collect($validated['items']);

        $invoiceTime = $invoice->created_at;
        $branchId = $invoice->branch_id;
        $invoiceDate = $invoiceTime->toDateString();
        $currentDate = now()->toDateString();

        $invoiceShift = ShiftClosing::where('branch_id', $branchId)
            ->where('start_time', '<=', $invoiceTime)
            ->where('end_time', '>=', $invoiceTime)
            ->first();

        $currentShift = ShiftClosing::where('branch_id', $branchId)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->first();

        $invoiceShiftId = optional($invoiceShift)->id;
        $currentShiftId = optional($currentShift)->id;

        // Totals
        $subTotal = 0;
        $totalQty = 0;
        $total = 0;

        foreach ($newItems as $item) {
            $productId = $item['product_id'];
            $newQty = (int) $item['quantity'];
            $newMRP = (float) $item['mrp'];
            $sell_price = (float) $item['sell_price'];
            $totalQty += $newQty;
            $subTotal += $newQty * $newMRP;
            $total += $newQty * $sell_price;

            $old = $currentItems->firstWhere('product_id', $productId);
            $oldQty = $old ? (int) $old['quantity'] : 0;
            $oldMRP = $old['mrp'] ?? 0;

            // ➕ New Product
            if (!$old) {
                InvoiceActivityLog::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'add',
                    'description' => 'Product added',
                    'new_data' => $item,
                    'user_id' => auth()->id(),
                ]);

                if ($invoiceShiftId) {
                    DailyProductStock::updateOrCreate(
                        [
                            'product_id' => $productId,
                            'branch_id' => $branchId,
                            'shift_id' => $invoiceShiftId,
                            'date' => $invoiceDate,
                        ],
                        [
                            'modify_sale_add_qty' => DB::raw('modify_sale_add_qty + ' . $newQty),
                        ]
                    );
                }

                continue;
            }

            // 🔼 Quantity Increased
            if ($newQty > $oldQty) {
                $diff = $newQty - $oldQty;

                InvoiceActivityLog::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'update',
                    'description' => "Qty increased: {$oldQty} → {$newQty}",
                    'old_data' => $old,
                    'new_data' => $item,
                    'user_id' => auth()->id(),
                ]);

                if ($invoiceShiftId) {
                    DailyProductStock::where([
                        'product_id' => $productId,
                        'branch_id' => $branchId,
                        'shift_id' => $invoiceShiftId,
                        'date' => $invoiceDate,
                    ])->increment('modify_sale_add_qty', $diff);
                }

                if ($currentShiftId) {
                    DailyProductStock::updateOrCreate(
                        [
                            'product_id' => $productId,
                            'branch_id' => $branchId,
                            'shift_id' => $currentShiftId,
                            'date' => $currentDate,
                        ],
                        [
                            'sold_stock' => DB::raw('sold_stock + ' . $diff),
                        ]
                    );
                }
            }

            // 🔽 Quantity Decreased
            elseif ($newQty < $oldQty) {
                $diff = $oldQty - $newQty;

                InvoiceActivityLog::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'update',
                    'description' => "Qty decreased: {$oldQty} → {$newQty}",
                    'old_data' => $old,
                    'new_data' => $item,
                    'user_id' => auth()->id(),
                ]);

                if ($invoiceShiftId) {
                    DailyProductStock::where([
                        'product_id' => $productId,
                        'branch_id' => $branchId,
                        'shift_id' => $invoiceShiftId,
                        'date' => $invoiceDate,
                    ])->increment('modify_sale_remove_qty', $diff);
                }

                if ($currentShiftId) {
                    DailyProductStock::updateOrCreate(
                        [
                            'product_id' => $productId,
                            'branch_id' => $branchId,
                            'shift_id' => $currentShiftId,
                            'date' => $currentDate,
                        ],
                        [
                            'added_stock' => DB::raw('added_stock + ' . $diff),
                        ]
                    );
                }
            }

            // ✏️ MRP Changed
            elseif ($newMRP != $oldMRP) {
                InvoiceActivityLog::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'update',
                    'description' => "Price changed: ₹{$oldMRP} → ₹{$newMRP}",
                    'old_data' => $old,
                    'new_data' => $item,
                    'user_id' => auth()->id(),
                ]);
            }
        }

        // ❌ Removed Products
        $currentItems->each(function ($item) use ($newItems, $invoice) {
            if (!$newItems->contains('product_id', $item['product_id'])) {
                InvoiceActivityLog::create([
                    'invoice_id' => $invoice->id,
                    'action' => 'remove',
                    'description' => 'Product removed',
                    'old_data' => $item,
                    'user_id' => auth()->id(),
                ]);
            }
        });

        // 💳 Credit change (Only for store_id = 1)
        $oldCredit = $invoice->creditpay ?? 0;
        $newCredit = $request->creditpay ?? 0;

        if ($oldCredit != $newCredit && $branchId == 1) {
            $diff = $newCredit - $oldCredit;

            // echo "Old Credit: {$oldCredit}, New Credit: {$newCredit}, Diff: {$diff}";
            // dd('sdf');

            $change = $diff > 0 ? "increased" : "decreased";
            $msg = "Credit {$change} from ₹{$oldCredit} to ₹{$newCredit}";



            $partyUser = PartyUser::where('status', 'Active')
                ->where('is_delete', 'No')
                ->where('id', $invoice->party_user_id) // use the foreign key
                ->first();

            if (!empty($partyUser)) {

                $partyUser->left_credit -= $diff;
                $partyUser->use_credit += $diff;

                // dd($partyUser);
                $partyUser->save();
            }

            InvoiceActivityLog::create([
                'invoice_id' => $invoice->id,
                'action' => 'credit_change',
                'description' => $msg,
                'old_data' => ['creditpay' => $oldCredit],
                'new_data' => ['creditpay' => $newCredit],
                'user_id' => auth()->id(),
            ]);
        }

        // 💾 Final Invoice Update
        $invoice->items = $validated['items'];
        $invoice->creditpay = $newCredit;
        $invoice->sub_total = $subTotal;
        $invoice->total_item_total = $subTotal;
        $invoice->total_item_qty = $totalQty;
        $invoice->total = $request->total;

        if ($branchId == 1) {
            $invoice->party_amount = $request->total_discount;
        } else {
            $invoice->commission_amount = $request->total_discount;
        }

        $invoice->invoice_status = $newCredit > 0 ? 'unpaid' : 'paid';
        $invoice->edit_in = 'yes';
        $invoice->save();
        return redirect()->route('sales.sales.list')->with('success', 'Invoice items updated successfully.');
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
                if (!empty($partyUser)) {
                    // $partyUser->use_credit = (int)$partyUser->use_credit + (int)$this->creditPay;
                    // $partyUser->left_credit = (int)$partyUser->credit_points - (int)$partyUser->use_credit;
                    // $partyUser->save();
                }
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
            foreach ($cartitems as $productId => $item) {

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

                if (!empty($currentShift)) {
                    stockStatusChange($item['product_id'], $branch_id, $item['quantity'], 'sold_stock', $currentShift->id);
                }

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

            if ($creditPay > 0 && $partyUser->id != "") {
                
                $partyUser = PartyUser::where('status', 'Active')
                    ->where('is_delete', 'No')
                    ->where('id', $partyUser->id) // use the foreign key
                    ->first();

                if (!empty($partyUser)) {

                    $partyUser->left_credit -= $creditPay;
                    $partyUser->use_credit += $creditPay;
                    $partyUser->save();
                }
            }

            \Log::info('Before Invoice Creation');
            $invoice = DB::table('invoices')->insert([
                'user_id' => Auth::id(),
                'branch_id' => $branch_id,
                'roundof' => $roundedTotal,
                'invoice_number' => $invoice_number,
                'commission_user_id' => $commissionUser->id ?? null,
                'party_user_id' => $partyUser->id ?? null,
                'payment_mode' => $paymentType,
                'items' => json_encode($cartitems),  // Ensure it's JSON encoded
                'total_item_qty' => $totalQuantity,
                'total_item_total' => $total_item_total,
                'upi_amount' => $upi,
                'change_amount' => $cashPayChangeAmt,
                'creditpay' => $creditPay,
                'cash_amount' => $cash,
                'online_amount' => $online_amount,
                'sub_total' => $sub_total,
                'status' => "Paid",
                'invoice_status' => $invoice_status,
                'commission_amount' => $commissionAmount,
                'party_amount' => $partyAmount,
                'total' => $cashAmount,
                'tax' => 0,
                'created_at' => $created_at,  // Manually set created_at
                'updated_at' => $created_at,  // Manually set updated_at
            ]);

            // \Log::info('Invoice Created: ', $invoice->toArray());
            // InvoiceHistory::logFromInvoice($invoice, 'created', Auth::id());
            DB::commit();
            return redirect()->route('sales.sales.list')->with('success', 'Invoice items updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transaction failed when creating user and wallet', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
