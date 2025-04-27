<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesReport extends Component
{
    public $range = 'month';  // Default range to 'month'
    public $start_date;
    public $end_date;

    public function render()
    {
        return view('livewire.sales-report');
    }

    // Method to fetch filtered data based on selected date range
    public function getFilteredData()
    {
        $query = DB::table('invoices')->where('status', 'Paid');

        switch ($this->range) {
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', Carbon::now()->subMonth());
                break;
            case 'year':
                $query->where('created_at', '>=', Carbon::now()->subYear());
                break;
            case 'custom':
                if ($this->start_date && $this->end_date) {
                    $query->whereBetween('created_at', [$this->start_date, $this->end_date]);
                }
                break;
        }

        $invoices = $query->get();
        $items = collect();

        foreach ($invoices as $invoice) {
            foreach (json_decode($invoice->items, true) as $item) {
                $items->push([
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'total' => $item['quantity'] * $item['price']
                ]);
            }
        }

        $grouped = $items->groupBy('name')->map(function ($g) {
            return [
                'name' => $g->first()['name'],
                'total_quantity' => $g->sum('quantity'),
                'total_amount' => $g->sum('total')
            ];
        })->values();

        return $grouped;
    }
}
