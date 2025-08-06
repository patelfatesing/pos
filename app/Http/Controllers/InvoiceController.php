<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\DB;

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
        $invoice->save();
        return redirect()->route('sales.sales.list')->with('success', 'Invoice items updated successfully.');
       
    }


    public function fetchHistory($id)
    {
        $invoice = Invoice::findOrFail($id);
        $logs = $invoice->activityLogs()->with('user')->latest()->get();

        return view('invoice.invoiceHistory', compact('logs'));
    }
}
