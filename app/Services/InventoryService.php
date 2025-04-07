<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    public function transferProduct($productId,$inventory_id, $fromId, $toId, $quantity,$type)
    {
        DB::beginTransaction();

        try {
            if($type != "add_stock") {

            
            // $fromInventory = Inventory::where([
            //     'product_id' => $productId,
            //     'id' => $inventory_id,
            //     'location_id' => $fromId
            // ])->lockForUpdate()->first();

            // if (!$fromInventory || $fromInventory->quantity < $quantity) {
            //     throw new Exception("Insufficient inventory at source.");
            // }

            // $fromInventory->decrement('quantity', $quantity);

            // $toInventory = Inventory::firstOrCreate([
            //     'product_id' => $productId,
            //     'location_id' => $toId
            // ], [
            //     'quantity' => 0
            // ]);

            // $toInventory->increment('quantity', $quantity);
            }

            InventoryTransaction::create([
                'product_id' => $productId,
                'inventory_id' => $inventory_id,
                'from_location_id' => $fromId,
                'to_location_id' => $toId ?: null,
                'quantity' => $quantity,
                'type' => $type
            ]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}