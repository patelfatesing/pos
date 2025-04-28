<?php

namespace App\Console\Commands;

use App\Models\Branch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LowStockNotification;
use App\Models\User;

class NotifyLowStock extends Command
{
    protected $signature = 'notify:lowstock';
    protected $description = 'Notify admin and stores about low stock products without changing inventory';

    public function handle()
    {
        // Only SELECT - no update or change
        $lowStocks = DB::table('inventories as i')
            ->join('products as p', 'p.id', '=', 'i.product_id')
            ->join('branches as b', 'b.id', '=', 'i.store_id')
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'i.store_id',
                'b.name as branch_name',
                'i.quantity',
                'p.reorder_level'
            )
            // ->where('p.is_deleted', 0)
            // ->where('p.is_active', 1)
            ->whereColumn('i.quantity', '<=', 'p.reorder_level')
            ->where('i.quantity', '>', 0)
            ->get();

        if ($lowStocks->isEmpty()) {
            $this->info('No low stock found.');
            return;
        }

        // Notify Admin
        // $admin = User::where('is_admin', 1)->first();
        // if ($admin) {
            // Notification::send($admin, new LowStockNotification($lowStocks));
        // }

        sendNotification('low_stock', 'Some Product is low level',null, 1,'');
        // Notify Store Managers
        $storeManagers = Branch::where('is_deleted', 'no')->get();

        foreach ($storeManagers as $manager) {
            $storeProducts = $lowStocks->where('store_id', $manager->id);

            if ($storeProducts->isNotEmpty()) {
                sendNotification('low_stock', $manager->name.' some Product is low level',$manager->id, 1,'');
                // Notification::send($manager, new LowStockNotification($storeProducts));
            }
        }

        $this->info('Low stock notifications sent successfully.');
    }
}
