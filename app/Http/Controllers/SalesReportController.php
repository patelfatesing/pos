<?php

// app/Http/Controllers/SalesReportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class SalesReportController extends Controller
{
    public function index()
    {
        $branches = DB::table('branches')->get(); // Adjust if you use a model
        return view('sales.index', compact('branches'));
    }

    public function salasList()
    {
        $branches = DB::table('branches')->get(); // Adjust if you use a model
        return view('sales.sales-list', compact('branches'));
    }

    public function getData(Request $request)
    {
        $query = DB::table('invoices')
        ->select('id', 'invoice_number', 'status', 'sub_total', 'tax', 'total', 'items', 'created_at');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $invoices = $query->get();

        $data = [];
        foreach ($invoices as $invoice) {
            $items = json_decode($invoice->items, true);
            $itemCount = collect($items)->sum('quantity');

            $action ='<div class="d-flex align-items-center list-action">
                    <a class="badge badge-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="View"
                    href="' . url('/view-invoice/' . $invoice->id) . '">'.$invoice->invoice_number.'</a>
            </div>';

            $data[] = [
                'invoice_number' => $action,
                'status' => $invoice->status,
                'sub_total' => number_format($invoice->sub_total, 2),
                'tax' => number_format($invoice->tax, 2),
                'total' => number_format($invoice->total, 2),
                'items_count' => $itemCount,
                'created_at' => Carbon::parse($invoice->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json(['data' => $data]);

    }

    public function storeSummary()
    {
        $invoices = DB::table('invoices')
            ->select('branch_id', 'items')
            ->where('status', '!=', 'cancelled') // or filter if needed
            ->get();

        $summary = [];

        foreach ($invoices as $invoice) {
            $items = json_decode($invoice->items, true);

            foreach ($items as $item) {
                $key = $invoice->branch_id . '|' . $item['name'];

                if (!isset($summary[$key])) {
                    $summary[$key] = [
                        'branch_id' => $invoice->branch_id,
                        'item_name' => $item['name'],
                        'total_quantity' => 0,
                        'total_amount' => 0,
                    ];
                }

                $quantity = (float) ($item['quantity'] ?? 1);
                $price = (float) ($item['price'] ?? 0);

                $summary[$key]['total_quantity'] += $quantity;
                $summary[$key]['total_amount'] += ($price * $quantity);
            }
        }

        // Re-index array
        $summary = array_values($summary);

        return response()->json($summary);
    }

    public function getSalesReportData(Request $request)
    {
        $query = DB::table('invoices')
            ->select(
                'invoices.id',
                'invoices.invoice_number',
                'invoices.sub_total',
                'invoices.tax',
                'invoices.total',
                'invoices.commission_amount', // <-- Added
                'invoices.party_amount',      // <-- Added
                'invoices.items',
                'invoices.status',
                'invoices.created_at',
                'branches.name as branch_name'
            )
            ->join('branches', 'invoices.branch_id', '=', 'branches.id');

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('invoices.created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        if ($request->branch_id) {
            $query->where('invoices.branch_id', $request->branch_id);
        }

        $invoices = $query->orderBy('branches.name')
            ->orderBy('invoices.created_at', 'desc')
            ->get()
            ->groupBy('branch_name');

        $finalData = [];
        $totalItemsOverall = 0;
        $totalAmountOverall = 0;
        $totalCommissionOverall = 0;
        $totalPartyAmountOverall = 0;

        foreach ($invoices as $branchName => $branchInvoices) {
            $branchInvoices = $branchInvoices->take(3); // Only 3 invoices per branch

            $total_sub_total = 0.0;
            $total_tax = 0.0;
            $total_total = 0.0;
            $total_items_count = 0;
            $total_commission = 0.0;
            $total_party_amount = 0.0;

            foreach ($branchInvoices as $invoice) {
                $items = json_decode($invoice->items, true);
                $items_count = count($items);
                $invoice_sub_total = 0.0;

                foreach ($items as $item) {
                    $invoice_sub_total += (float)$item['quantity'] * (float)$item['price'];
                }

                // Add each invoice data to finalData
                $finalData[] = [
                    'branch_name'        => $branchName,
                    'invoice_number'     => $invoice->invoice_number,
                    'status'             => $invoice->status,
                    'sub_total'          => $invoice_sub_total,
                    'tax'                => (float)$invoice->tax,
                    'commission_amount'  => (float)$invoice->commission_amount,
                    'party_amount'       => (float)$invoice->party_amount,
                    'total'              => (float)$invoice->total,
                    'items_count'        => $items_count,
                    'created_at'         => Carbon::parse($invoice->created_at)->format('Y-m-d'),
                    'colspan'            => 9
                ];

                // Update totals
                $total_sub_total += $invoice_sub_total;
                $total_tax += (float)$invoice->tax;
                $total_commission += (float)$invoice->commission_amount;
                $total_party_amount += (float)$invoice->party_amount;
                $total_total += (float)$invoice->total;
                $total_items_count += $items_count;
            }

            // Add summary row for this branch
            $finalData[] = [
                'branch_name'        => $branchName . ' (Summary)',
                'invoice_number'     => '',
                'status'             => '',
                'sub_total'          => $total_sub_total,
                'tax'                => $total_tax,
                'commission_amount'  => $total_commission,
                'party_amount'       => $total_party_amount,
                'total'              => $total_total,
                'items_count'        => $total_items_count,
                'created_at'         => '',
                'colspan'            => 8
            ];

            // Track overall totals
            $totalItemsOverall += $total_items_count;
            $totalAmountOverall += $total_total;
            $totalCommissionOverall += $total_commission;
            $totalPartyAmountOverall += $total_party_amount;
        }

        // Optionally: Grand Total
        // $finalData[] = [
        //     'branch_name'        => 'Grand Total',
        //     'invoice_number'     => '',
        //     'status'             => '',
        //     'sub_total'          => '',
        //     'tax'                => '',
        //     'commission_amount'  => $totalCommissionOverall,
        //     'party_amount'       => $totalPartyAmountOverall,
        //     'total'              => $totalAmountOverall,
        //     'items_count'        => $totalItemsOverall,
        //     'created_at'         => '',
        //     'colspan'            => 5
        // ];

        return response()->json([
            'data' => $finalData
        ]);
    }

    
          
}
