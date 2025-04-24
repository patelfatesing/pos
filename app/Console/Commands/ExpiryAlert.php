<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpiryAlert extends Command
{
    protected $signature = 'alert:expiry';
    protected $description = 'Send alerts for products expiring in 5 days';

    public function handle()
    {
        $targetDate = Carbon::now()->addDays(5)->format('Y-m-d');

        $expiringInventories = DB::table('inventories')
            ->whereDate('expiry_date', $targetDate)
            ->get();

        foreach ($expiringInventories as $inventory) {
            // You can replace this with email, notification, logging, etc.
            Log::info("Product ID {$inventory->product_id} is expiring on {$inventory->expiry_date}");

            // Example: Send notification or alert
            // Notification::route(...)->notify(new ExpiryAlertNotification($inventory));
        }

        $this->info("Expiry alerts processed for products expiring on {$targetDate}.");
    }
}
