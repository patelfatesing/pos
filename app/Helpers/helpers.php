<?php

use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Events\DrawerOpened;
use App\Models\User;
use App\Models\Product;
use App\Models\PartyCustomerProductsPrice;
use App\Models\DailyProductStock;
use App\Models\ShiftClosing;
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
    function stockStatusChange($product_id, $branch_id, $qty, $type, $shift_id = "", $orderType = "")
    {
        $date = Carbon::today();

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

function closingStock(float|int $opening = 0, float|int $added = 0, float|int $transferred = 0, float|int $sold = 0)
{
    return ($opening + $added - $transferred - $sold);
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
