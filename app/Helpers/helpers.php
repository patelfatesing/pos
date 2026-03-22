<?php

use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Events\DrawerOpened;
use App\Models\User;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PartyCustomerProductsPrice;
use App\Models\DailyProductStock;
use App\Models\ShiftClosing;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

if (!function_exists('pre')) {
    function pre($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit;
    }
}

if (!function_exists('sendNotification')) {
    function sendNotification($type, $content, $notifyTo, $createdBy, $details = null, $priority = 0)
    {
        $userData = User::with(['userInfo'])->where('users.id', $createdBy)->where('is_deleted', 'no')->firstOrFail();

        $notification = Notification::create([
            'type' => $type,
            'content' => $content,
            'details' => $details,
            'notify_to' => $notifyTo,
            'created_by' => $createdBy,
            'priority' => $priority,
        ]);

        $data = json_decode($details, true);

        event(new DrawerOpened([
            'notify_to' => $notifyTo,
            'message' => $content,
            'customer' => $userData->name,
            'type' => $type,
            'value' => ($type == 'low_stock' ? '' : ''),
            'nfid' => $notification->id, // You can pass real customer data here
        ]));
    }
}

if (!function_exists('getNotificationsByNotifyTo')) {
    function getNotificationsByNotifyTo($userId, $branch_id, $limit = 50)
    {
        $query = Notification::where('created_at', '>=', now()->subDay());

        if ($branch_id != "") {
            $query->where('notify_to', $branch_id);
        } else {
            $query->whereNull('notify_to');
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('getNotificationsByCreatedBy')) {
    function getNotificationsByCreatedBy($userId, $limit = 10)
    {
        return Notification::where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('getUnreadNotificationsByNotifyTo')) {
    function getUnreadNotificationsByNotifyTo($userId, $branch_id, $limit = 50)
    {
        if ($branch_id != "") {
            return Notification::where('status', 'unread')
                ->where('notify_to', $branch_id)
                ->where('created_at', '>=', Carbon::now()->subDay())  // Notifications within last 24 hours
                ->count();
        } else {
            return Notification::where('notify_to', null)
                ->where('status', 'unread')
                ->count();
        }
    }
}

if (!function_exists('getNotificationsByIdData')) {
    function getNotificationsByIdData($id)
    {
        return Notification::where('id', $id)
            ->first();
    }
}

if (!function_exists('updateUnreadNotificationsById')) {
    function updateUnreadNotificationsById($id)
    {
        return Notification::where('id', $id)
            ->where('status', 'unread') // optional, to only update if unread
            ->update(['status' => 'read']);
    }
}

if (!function_exists('format_inr')) {
    function format_inr($amount)
    {
        // Remove commas or non-numeric characters (except minus and decimal point)
        $cleanAmount = preg_replace('/[^\d.-]/', '', $amount);

        // Convert to float
        $numericAmount = (float) $cleanAmount;

        $sign = $numericAmount < 0 ? '-' : '';
        return $sign . '₹' . number_format(abs($numericAmount), 0);
    }
}

if (!function_exists('round_up_to_nearest_10')) {
    function round_up_to_nearest_10($number)
    {
        return ceil($number / 10) * 10;
    }
}

if (!function_exists('parseCurrency')) {
    function parseCurrency($value)
    {
        // Remove commas and convert to float or int
        return (float) str_replace(',', '', $value);
    }
}

if (!function_exists('getDiscountPrice')) {
    function getDiscountPrice($product_id, $party_user_id, $selectedCommissionUser = false)
    {
        $partyUserDiscountAmt = 0;
        $commissionAmount = 0;
        $partyAmount = 0;

        if ($selectedCommissionUser) {
            $product = Product::find($product_id);
            if ($product) {
                $partyUserDiscountAmt = $commissionAmount = $product->discount_price;
            }
        } else {
            $partyCustomerProductsPrice = PartyCustomerProductsPrice::with('product')
                ->where('product_id', $product_id)
                ->where('party_user_id', $party_user_id)
                ->first();
            if ($partyCustomerProductsPrice) {
                $discount = $partyCustomerProductsPrice->product->sell_price - $partyCustomerProductsPrice->cust_discount_price;
                $partyUserDiscountAmt = $partyAmount = $discount;
            }
        }

        Log::info("partyUserDiscountAmt::::" . $partyUserDiscountAmt);

        return [
            'partyUserDiscountAmt' => $partyUserDiscountAmt,
            'commissionAmount' => $commissionAmount,
            'partyAmount' => $partyAmount,
        ];
    }
}

if (!function_exists('stockStatusChange')) {
    function stockStatusChange($product_id, $branch_id, $qty, $type, $shift_id = "", $orderType = "", $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        if ($type == "add_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                // ->whereDate('date', $date)
                ->where('shift_id', $shift_id)
                ->first();

            if (!empty($existing)) {
                $existing->added_stock += $qty;
                if ($orderType == "refunded_order") {
                    $existing->sold_stock -= $qty;
                    $existing->closing_stock += $qty;
                } else {

                    $existing->closing_stock = closingStock($existing->opening_stock, $existing->added_stock, $existing->transferred_stock, $existing->sold_stock);
                }
                $existing->save();
            } else {

                $running_shift = ShiftClosing::where('branch_id', $branch_id)
                    ->where('status', 'pending')
                    ->first();

                if (!empty($running_shift)) {
                    $existing_ck = DailyProductStock::where('branch_id', $branch_id)
                        ->where('shift_id', $running_shift->id)
                        ->where('product_id', $product_id)
                        ->first();
                    if (!empty($existing_ck)) {
                        $existing_ck->added_stock += $qty;
                        $existing_ck->shift_id = $running_shift->id;
                        $existing_ck->closing_stock = closingStock($existing_ck->opening_stock, $existing_ck->added_stock, $existing_ck->transferred_stock, $existing_ck->sold_stock);
                        $existing_ck->save();
                    } else {
                        DailyProductStock::create([
                            'branch_id' => $branch_id,
                            'product_id' => $product_id,
                            'date' => $date,
                            'opening_stock' => $qty,
                            'closing_stock' => $qty,
                            'shift_id' => $running_shift->id
                        ]);
                    }
                } else {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'opening_stock' => $qty,
                        'closing_stock' => $qty,
                    ]);
                }
            }
        }

        if ($type == "transfer_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                // ->whereDate('date', $date)
                ->where('shift_id', $shift_id)
                ->first();

            if (! empty($existing)) {
                $existing->transferred_stock += $qty;
                $existing->closing_stock = closingStock($existing->opening_stock, $existing->added_stock, $existing->transferred_stock, $existing->sold_stock);
                $existing->save();
            } else {

                $running_shift = ShiftClosing::where('branch_id', $branch_id)
                    ->where('status', 'pending')
                    ->first();


                if (!empty($running_shift)) {

                    $existing_ck = DailyProductStock::where('branch_id', $branch_id)
                        ->where('shift_id', $running_shift->id)
                        ->where('product_id', $product_id)
                        ->first();

                    if (!empty($existing_ck)) {
                        $existing_ck->transferred_stock += $qty;
                        $existing_ck->shift_id = $running_shift->id;
                        $existing_ck->closing_stock = closingStock($existing_ck->opening_stock, $existing_ck->added_stock, $existing_ck->transferred_stock, $existing_ck->sold_stock);
                        $existing_ck->save();
                    } else {
                        DailyProductStock::create([
                            'branch_id' => $branch_id,
                            'product_id' => $product_id,
                            'date' => $date,
                            'opening_stock' => $qty,
                            'closing_stock' => $qty,
                            'shift_id' => $running_shift->id
                        ]);
                    }
                } else {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'opening_stock' => $qty,
                        'closing_stock' => $qty,
                    ]);
                }
            }
        }

        if ($type == "sold_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                // ->whereDate('date', $date)
                ->where('shift_id', $shift_id)
                ->first();

            if (!empty($existing)) {
                $existing->sold_stock += $qty;
                $existing->closing_stock = closingStock($existing->opening_stock, $existing->added_stock, $existing->transferred_stock, $existing->sold_stock);
                $existing->save();
            } else {
                if ($shift_id == "") {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'sold_stock' => $qty,
                        'closing_stock' => $qty,
                    ]);
                } else {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'sold_stock' => $qty,
                        'closing_stock' => $qty,
                        'shift_id' => $shift_id
                    ]);
                }
            }
        }

        if ($type == "add_modify_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                // ->whereDate('date', $date)
                ->where('shift_id', $shift_id)
                ->first();

            if (!empty($existing)) {
                $existing->modify_sale_remove_qty += $qty;
                $existing->sold_stock = $existing->sold_stock - $qty;
                $existing->closing_stock = $existing->opening_stock + $qty;
                $existing->save();
            } else {
                if ($shift_id == "") {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'modify_sale_remove_qty' => $qty
                    ]);
                } else {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'modify_sale_remove_qty' => $qty,
                        'shift_id' => $shift_id
                    ]);
                }
            }
        }

        if ($type == "remove_modify_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                // ->whereDate('date', $date)
                ->where('shift_id', $shift_id)
                ->first();

            if (!empty($existing)) {
                $existing->modify_sale_remove_qty += $qty;
                $existing->sold_stock = $existing->sold_stock - $qty;
                $existing->closing_stock = $existing->closing_stock + $qty;
                $existing->save();
            } else {
                if ($shift_id == "") {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'modify_sale_add_qty' => $qty
                    ]);
                } else {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'modify_sale_add_qty' => $qty,
                        'shift_id' => $shift_id
                    ]);
                }
            }
        }

        if ($type == "remove_add_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                ->where('shift_id', $shift_id)
                ->first();

            if ($existing) {

                if ($orderType == "refunded_order") {

                    // ❌ DO NOT decrease sold_stock
                    // $existing->sold_stock -= $qty;

                    // ✅ Track in modify remove
                    $existing->modify_sale_remove_qty += $qty;
                } else {

                    // ❌ DO NOT touch opening_stock
                    // $existing->opening_stock += $qty;

                    // ✅ Track in modify add
                    $existing->modify_sale_add_qty += $qty;
                }

                // ✅ Correct closing stock calculation
                $existing->closing_stock = closingStock(
                    $existing->opening_stock,
                    $existing->added_stock,
                    $existing->transferred_stock,
                    $existing->sold_stock
                        + $existing->modify_sale_add_qty
                        - $existing->modify_sale_remove_qty
                );

                $existing->save();
            } else {

                $running_shift = ShiftClosing::where('branch_id', $branch_id)
                    ->where('status', 'pending')
                    ->first();

                if ($running_shift) {

                    $existing_ck = DailyProductStock::where('branch_id', $branch_id)
                        ->where('shift_id', $running_shift->id)
                        ->where('product_id', $product_id)
                        ->first();

                    if ($existing_ck) {

                        if ($orderType == "refunded_order") {

                            $existing_ck->modify_sale_remove_qty += $qty;
                        } else {

                            $existing_ck->modify_sale_add_qty += $qty;
                        }

                        $existing_ck->closing_stock = closingStock(
                            $existing_ck->opening_stock,
                            $existing_ck->added_stock,
                            $existing_ck->transferred_stock,
                            $existing_ck->sold_stock
                                + $existing_ck->modify_sale_add_qty
                                - $existing_ck->modify_sale_remove_qty
                        );

                        $existing_ck->save();
                    } else {

                        DailyProductStock::create([
                            'branch_id' => $branch_id,
                            'product_id' => $product_id,
                            'date' => $date,
                            'shift_id' => $running_shift->id,
                            'opening_stock' => 0,
                            'closing_stock' => 0,
                            'modify_sale_add_qty' => $orderType == "add_order" ? $qty : 0,
                            'modify_sale_remove_qty' => $orderType == "refunded_order" ? $qty : 0,
                        ]);
                    }
                } else {

                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'opening_stock' => 0,
                        'closing_stock' => 0,
                        'modify_sale_add_qty' => $orderType == "add_order" ? $qty : 0,
                        'modify_sale_remove_qty' => $orderType == "refunded_order" ? $qty : 0,
                    ]);
                }
            }
        }

        if ($type == "remove_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                ->where('shift_id', $shift_id)
                ->first();

            if (!empty($existing)) {

                $existing->modify_sale_remove_qty += $qty;

                $existing->closing_stock = closingStock(
                    $existing->opening_stock,
                    $existing->added_stock,
                    $existing->transferred_stock,
                    $existing->sold_stock
                );

                $existing->save();
            } else {

                DailyProductStock::create([
                    'branch_id' => $branch_id,
                    'product_id' => $product_id,
                    'date' => $date,
                    'modify_sale_remove_qty' => $qty,
                    'closing_stock' => 0,
                    'shift_id' => $shift_id
                ]);
            }
        }
    }
}

if (!function_exists('getProductStockQuery')) {
    function getProductStockQuery()
    {
        return DB::table('inventories')
            ->select(
                'inventories.product_id',
                DB::raw('MAX(products.name) as name'),
                DB::raw('MAX(products.size) as size'),
                DB::raw('MAX(categories.name) as category_name'),
                DB::raw('MAX(sub_categories.name) as subcategory_name'),
                DB::raw('SUM(inventories.quantity) as current_stock'),
                DB::raw('MAX(products.reorder_level) as reorder_level')
            )
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('sub_categories', 'products.subcategory_id', '=', 'sub_categories.id')
            ->join('branches', 'inventories.store_id', '=', 'branches.id') // assuming branch_id in inventories
            ->where('branches.is_warehouser', 'yes')
            ->groupBy('inventories.product_id');
    }
}


if (!function_exists('stockRealtimeUpdate')) {

    function stockRealtimeUpdate($product_id, $branch_id, $qty, $type, $shift_id = "")
    {
        $date = Carbon::today();

        if ($type == "add_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                ->where('shift_id', $shift_id)
                ->first();


            if (!empty($existing)) {
                $existing->added_stock += $qty;
                $existing->closing_stock = closingStock($existing->opening_stock, $existing->added_stock, $existing->transferred_stock, $existing->closing_stock);
                $existing->save();
            } else {

                $running_shift = ShiftClosing::where('branch_id', $branch_id)
                    ->where('status', 'pending')
                    ->first();

                if (!empty($running_shift)) {
                    $existing_ck = DailyProductStock::where('branch_id', $branch_id)
                        ->where('shift_id', $running_shift->id)
                        ->where('product_id', $product_id)
                        ->first();

                    if (!empty($existing_ck)) {

                        $existing_ck->added_stock += $qty;
                        $existing_ck->closing_stock = closingStock($existing_ck->opening_stock, $existing_ck->added_stock, $existing_ck->transferred_stock, $existing_ck->closing_stock);
                        $existing_ck->save();
                    } else {
                        DailyProductStock::create([
                            'branch_id' => $branch_id,
                            'product_id' => $product_id,
                            'date' => $date,
                            'opening_stock' => $qty,
                            'closing_stock' => $qty,
                            'shift_id' => $running_shift->id
                        ]);
                    }
                } else {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'opening_stock' => $qty,
                        'closing_stock' => $qty
                    ]);
                }
            }
        }


        if ($type == "transfer_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                ->where('shift_id', $shift_id)
                ->first();

            if (! empty($existing)) {
                $existing->transferred_stock += $qty;
                $existing->closing_stock = closingStock($existing->opening_stock, $existing->added_stock, $existing->transferred_stock, $existing->closing_stock);
                $existing->save();
            } else {

                $running_shift = ShiftClosing::where('branch_id', $branch_id)
                    ->where('status', 'pending')
                    ->first();


                if (!empty($running_shift)) {

                    $existing_ck = DailyProductStock::where('branch_id', $branch_id)
                        ->where('shift_id', $running_shift->id)
                        ->where('product_id', $product_id)
                        ->first();

                    if (!empty($existing_ck)) {
                        $existing_ck->transferred_stock += $qty;
                        $existing_ck->closing_stock = closingStock($existing_ck->opening_stock, $existing_ck->added_stock, $existing_ck->transferred_stock, $existing_ck->closing_stock);
                        $existing_ck->save();
                    } else {
                        DailyProductStock::create([
                            'branch_id' => $branch_id,
                            'product_id' => $product_id,
                            'date' => $date,
                            'opening_stock' => $qty,
                            'closing_stock' => $qty,
                            'shift_id' => $running_shift->id
                        ]);
                    }
                }
            }
        }

        if ($type == "sold_stock") {

            $existing = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)
                ->where('shift_id', $shift_id)
                ->first();

            if (!empty($existing)) {
                $existing->sold_stock += $qty;
                $existing->closing_stock = closingStock($existing->opening_stock, $existing->added_stock, $existing->transferred_stock, $existing->closing_stock);
                $existing->save();
            } else {
                if ($shift_id == "") {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'sold_stock' => $qty,
                        'closing_stock' => $qty
                    ]);
                } else {
                    DailyProductStock::create([
                        'branch_id' => $branch_id,
                        'product_id' => $product_id,
                        'date' => $date,
                        'sold_stock' => $qty,
                        'closing_stock' => $qty,
                        'shift_id' => $shift_id
                    ]);
                }
            }
        }
    }
}

if (!function_exists('recalculateStockFromDate')) {

    function recalculateStockFromDate($product_id, $branch_id, $fromDate)
    {
        $stocks = DailyProductStock::where('product_id', $product_id)
            ->where('branch_id', $branch_id)
            ->whereDate('date', '>=', Carbon::parse($fromDate)->subDay())
            ->orderBy('date', 'asc')
            ->orderBy('shift_id', 'asc')
            ->get();

        $prevClosing = null;

        foreach ($stocks as $index => $stock) {

            // ✅ FIRST ROW FIX
            if ($index == 0) {
                $prevClosing = $stock->closing_stock;

                // 🔥 ADD THIS (missing part)
                $stock->physical_stock = $stock->closing_stock;
                $stock->difference_in_stock = 0;

                $stock->save();
                continue;
            }

            // Normal flow
            $stock->opening_stock = $prevClosing;

            $stock->sold_stock = max(0, $stock->sold_stock);

            $finalSold = $stock->sold_stock
                + $stock->modify_sale_add_qty
                - $stock->modify_sale_remove_qty;

            $stock->closing_stock =
                $stock->opening_stock
                + $stock->added_stock
                + $stock->transferred_stock
                - $finalSold;

            $stock->physical_stock = $stock->closing_stock;

            $stock->difference_in_stock =
                $stock->physical_stock - $stock->closing_stock;

            $stock->save();

            $prevClosing = $stock->closing_stock;
        }
    }
}

if (!function_exists('updateInventoryStock')) {

    function updateInventoryStock($productId, $branchId, $qty, $type)
    {
        $inventory = Inventory::firstOrCreate(
            [
                'product_id' => $productId,
                'store_id' => $branchId,
            ],
            [
                'quantity' => 0
            ]
        );

        if ($type == 'sale') {
            $inventory->quantity -= $qty;
        }

        if ($type == 'refund') {
            $inventory->quantity += $qty;
        }

        // optional safety
        $inventory->quantity = max(0, $inventory->quantity);

        $inventory->save();
    }
}

if (!function_exists('stockStatusChangeNew')) {
    function stockStatusChangeNew($product_id, $branch_id, $qty, $type, $shift_id)
    {
        $shift = ShiftClosing::find($shift_id);
        $date = Carbon::parse($shift->start_time)->toDateString();

        $stock = DailyProductStock::firstOrCreate(
            [
                'product_id' => $product_id,
                'branch_id'  => $branch_id,
                'shift_id'   => $shift_id,
                'date'       => $date,
            ],
            [
                'opening_stock' => 0,
                'closing_stock' => 0,
            ]
        );

        // 🔥 ONLY SOLD STOCK LOGIC
        if ($type == 'sold_stock') {
            $stock->sold_stock += $qty;
        }

        if ($type == 'refunded_order') {
            $stock->sold_stock -= $qty;
        }

        // prevent negative
        $stock->sold_stock = max(0, $stock->sold_stock);

        // recalc closing
        $stock->closing_stock =
            $stock->opening_stock
            + $stock->added_stock
            + $stock->transferred_stock
            - $stock->sold_stock;

        $stock->save();
    }
}

function closingStock(float|int $opening = 0, float|int $added = 0, float|int $transferred = 0, float|int $sold = 0)
{
    return ($opening + $added - $transferred - $sold);
}

if (!function_exists('logActivity')) {

    function logActivity(
        $type = null,
        $action = null,
        $message = null,
        $old = [],
        $new = []
    ) {
        try {

            ActivityLog::create([
                'type' => $type,
                'action' => $action,
                'message' => $message,

                'old_data' => !empty($old) ? json_encode($old) : null,
                'new_data' => !empty($new) ? json_encode($new) : null,

                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),

                'user_id' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Activity Log Error: ' . $e->getMessage());
        }
    }
}


// if (!function_exists('getNotificationsByNotifyTo')) {
//     function getNotificationsByNotifyTo($userId, $branch_id, $limit = 50)
//     {
//         if ($branch_id != "") {
//             return Notification::where('notify_to', $branch_id)
//                 ->orderBy('created_at', 'desc')
//                 ->limit($limit)
//                 ->get();
//         } else {
//             return Notification::where('notify_to', null)
//                 ->orderBy('created_at', 'desc')
//                 ->limit($limit)
//                 ->get();
//         }
//     }
// }
