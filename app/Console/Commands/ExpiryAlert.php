<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory;
use App\Models\Branch;

class ExpiryAlert extends Command
{
    protected $signature = 'alert:expiry';
    protected $description = 'Send alerts for products expiring in 5 days';

    public function handle()
    {
        // Get the expiring products
        $expiringProducts = DB::select("
            SELECT 
                p.id AS product_id,
                p.name AS product_name,
                p.brand,
                p.size,
                p.sku,
                p.abv,
                p.barcode,
                p.image,
                p.description,
                p.category_id,
                p.subcategory_id,
                p.is_active,
                p.is_deleted,
                p.created_at AS product_created_at,
                p.updated_at AS product_updated_at,
                p.reorder_level,
                p.cost_price,
                p.sell_price,
                p.price_apply_date,
                p.discount_price,
                p.discount_amt,
                p.case_size,
                p.box_unit,
                p.secondary_unitx,
                b.id AS branch_id,
                b.name AS branch_name,
                b.is_warehouser,
                b.address,
                b.description AS branch_description,
                b.is_active AS branch_is_active,
                b.is_deleted AS branch_is_deleted,
                b.created_at AS branch_created_at,
                b.updated_at AS branch_updated_at,
                i.id AS inventory_id,
                i.product_id AS inventory_product_id,
                i.store_id,
                i.location_id,
                i.type,
                i.batch_no,
                i.expiry_date,
                i.mfg_date,
                i.quantity AS inventory_quantity,
                i.created_at AS inventory_created_at,
                i.updated_at AS inventory_updated_at,
                i.vendor_id,
                i.added_by
            FROM 
                products p
            JOIN 
                inventories i ON i.product_id = p.id
            JOIN 
                branches b ON i.store_id = b.id
            WHERE 
                i.expiry_date IS NOT NULL
                AND i.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)
                AND i.quantity > 0
        ");
    
        if (count($expiringProducts) > 0) {
            // Group products by store_id
            $productsByStore = collect($expiringProducts)->groupBy('branch_id');
    
            // Notify Admin about all expiring products
            // Notification::route('mail', 'admin@example.com')->notify(new ExpiryProductNotification($expiringProducts));
            sendNotification('expire_product', 'Some Product is expire after some few days',null, 1,'');
            // Notify store managers
            foreach ($productsByStore as $storeId => $products) {
                $store = Branch::find($storeId);
                if ($store && $store->name) {
                    // Send only one email per store with the grouped expiring products
                    // Notification::route('mail', $store->manager->email)
                    //     ->notify(new ExpiryProductNotification($products));
                    sendNotification('expire_product', $store->name.' some Product is expire after some few days',$store->id, 1,'');
                }
            }
            $this->info('Expiry notifications sent to Admin and Store Managers.');
        } else {
            $this->info('No products expiring within 5 days.');
        }
    }
    
}
