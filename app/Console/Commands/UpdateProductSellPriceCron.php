<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateProductSellPriceCron extends Command
{
    protected $signature = 'product:update-sell-price';
    protected $description = 'Update product sell_price if changed_at is today in price change history';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // Get product price changes where changed_at = today
        $priceChanges = DB::table('product_price_change_history')
            ->whereDate('changed_at', $today)
            ->get();

        foreach ($priceChanges as $change) {
            DB::table('products')
                ->where('id', $change->product_id)
                ->update([
                    'sell_price' => $change->new_price,
                    'price_apply_date' => $today,
                    'updated_at' => now()
                ]);

            $this->info("Updated product ID {$change->product_id} to new price {$change->new_price}");
        }

        $this->info('Product prices updated successfully.');
    }
}

