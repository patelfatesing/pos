<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyProductStock;
use App\Models\Product;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateDailyStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-daily-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = Carbon::yesterday();

        $branches = Branch::all();
        $products = Product::all();

        foreach ($branches as $branch) {
            foreach ($products as $product) {
                
            $openingStock = DB::table('inventories')
                ->where('product_id', $product->id)
                ->where('store_id', $branch->id)
                ->sum('quantity');

                DailyProductStock::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'branch_id' => $branch->id,
                        'date' => $date,
                        'opening_stock' => $openingStock,
                    ]
                );
            }
        }

        $this->info('Daily stock initialized successfully.');
    }
}
