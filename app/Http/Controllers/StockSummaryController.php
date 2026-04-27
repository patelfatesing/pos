<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\SubCategory;
use Carbon\Carbon;

class StockSummaryController extends Controller
{
public function stockSummary()
    {
        $branches = DB::table('branches')->pluck('name', 'id');
        return view('stock_summary.stock_summary', compact('branches'));
    }

 public function stockSummaryData(Request $request)
{
    $fromDate = $request->from_date;
    $toDate   = $request->to_date;

    $data = DB::table('daily_product_stocks as dps')
        ->join('products as p', 'p.id', '=', 'dps.product_id')
        ->join('sub_categories as sc', 'sc.id', '=', 'p.subcategory_id')
        ->select(
            'p.name as product',
            'sc.name as category',

            // OPENING (first day total of all branches)
            DB::raw("
                SUM(CASE 
                    WHEN dps.date = '$fromDate' THEN dps.opening_stock 
                    ELSE 0 
                END) as opening
            "),

            // INWARD (ALL branches)
            DB::raw("SUM(dps.added_stock + dps.transferred_stock) as inward"),

            // OUTWARD
            DB::raw("SUM(dps.sold_stock) as outward"),

            // ADJUSTMENT
            DB::raw("SUM(dps.modify_sale_add_qty - dps.modify_sale_remove_qty) as adjustment"),

            // CLOSING (last day total)
            DB::raw("
                SUM(CASE 
                    WHEN dps.date = '$toDate' THEN dps.closing_stock 
                    ELSE 0 
                END) as closing
            ")
        )
        ->whereBetween('dps.date', [$fromDate, $toDate])
        ->groupBy('p.id', 'p.name', 'sc.name')
        ->orderBy('sc.name')
        ->orderBy('p.name')
        ->get();

    return response()->json(['data' => $data]);
}
}
