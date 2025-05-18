<?php

use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Events\DrawerOpened;
use App\Models\User;
use App\Models\Product;
use App\Models\PartyCustomerProductsPrice;
use App\Models\DailyProductStock;
use Carbon\Carbon;

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

        $data = json_decode($details);
         event(new DrawerOpened([
            'notify_to' => $notifyTo,
            'message' => $content,
            'customer' => $userData->name,
            'type' => $type,
            'value' => $data->id,
            'nfid' => $notification->id, // You can pass real customer data here
        ]));
    }
}

if (!function_exists('getNotificationsByNotifyTo')) {
    function getNotificationsByNotifyTo($userId,$branch_id, $limit = 50)
    {
        if($branch_id != ""){
            return Notification::where('notify_to', $branch_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        }else{
            return Notification::where('notify_to', null)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        }

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
    function getUnreadNotificationsByNotifyTo($userId,$branch_id, $limit = 50)
    {
        if($branch_id != ""){
            return Notification::where('status', 'unread')
            ->where('notify_to', $branch_id)
            ->count();
        }else{
            return Notification::where('notify_to', null)
            ->where('status', 'unread')
            ->count();
        }

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
        return $sign . '₹' . number_format(abs($numericAmount), 2);
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
                $discount=$partyCustomerProductsPrice->product->sell_price-$partyCustomerProductsPrice->cust_discount_price;
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
    function stockStatusChange($product_id, $branch_id, $qty, $type)
        {
            $date = Carbon::today();

            if ($type == "add_stock") {

                $existing = DailyProductStock::where('branch_id', $branch_id)
                    ->where('product_id', $product_id)
                    ->whereDate('date', $date)
                    ->first();

                if (!empty($existing)) {
                    $existing->added_stock += $qty;
                    $existing->save();
                } else {
                    DailyProductStock::create([
                            'branch_id' => $branch_id,
                            'product_id' => $product_id,
                            'date' => $date,
                            'added_stock' => $qty
                        ]);
                }
            }

            if ($type == "transfer_stock") {

                $existing = DailyProductStock::where('branch_id', $branch_id)
                    ->where('product_id', $product_id)
                    ->whereDate('date', $date)
                    ->first();

                if (! empty($existing)) {
                    $existing->transferred_stock += $qty;
                    $existing->save();
                } else {
                    DailyProductStock::create([
                            'branch_id' => $branch_id,
                            'product_id' => $product_id,
                            'date' => $date,
                            'transferred_stock' => $qty
                        ]);
                }
            }

            if ($type == "sold_stock") {

                $existing = DailyProductStock::where('branch_id', $branch_id)
                    ->where('product_id', $product_id)
                    ->whereDate('date', $date)
                    ->first();

                if (! empty($existing)) {
                    $existing->sold_stock += $qty;
                    $existing->save();
                } else {
                    DailyProductStock::create([
                            'branch_id' => $branch_id,
                            'product_id' => $product_id,
                            'date' => $date,
                            'sold_stock' => $qty
                        ]);
                }
            }

        }
}
