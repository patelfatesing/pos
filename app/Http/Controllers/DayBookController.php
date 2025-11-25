<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Payment; // if you have separate payments
use Carbon\Carbon;
use Illuminate\Support\Facades\View;

class DayBookController extends Controller
{
    public function index(Request $request)
    {
        // Date filters (default = today)
        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());

        $from = Carbon::parse($fromDate)->startOfDay();
        $to   = Carbon::parse($toDate)->endOfDay();

        // Optional branch filter (if you use branches)
        $branchId = $request->input('branch_id');

        // --------- 1. CASH IN FROM SALES (INVOICES) ----------
        $invoiceQuery = Invoice::query()
            ->whereBetween('created_at', [$from, $to]);

        if ($branchId) {
            $invoiceQuery->where('branch_id', $branchId);
        }

        // Example: in your invoices table you already have:
        // cash_amount, upi_amount, online_amount
        $invoices = $invoiceQuery->get();
        // dd($invoices);

        $dayBookEntries = collect();

        foreach ($invoices as $invoice) {
            $cashIn = $invoice->cash_amount + $invoice->upi_amount + $invoice->online_amount;

            if ($cashIn > 0) {
                $dayBookEntries->push([
                    'id'          => $invoice->id,
                    'date'        => $invoice->created_at,
                    'voucher_no'  => $invoice->invoice_number ?? $invoice->id,
                    'voucher_type' => 'invoice',
                    'ledger'      => $invoice->party_user_id ? 'Customer #' . $invoice->party_user_id : 'Cash / POS Sale',
                    'debit'       => $cashIn,   // Cash in
                    'credit'      => 0,
                    'remarks'     => 'Invoice #' . ($invoice->invoice_number ?? $invoice->id),
                ]);
            }
        }

        // --------- 2. CASH OUT – EXPENSES ----------
        $expenseQuery = Expense::query()
            ->whereBetween('created_at', [$from, $to]);

        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }

        $expenses = $expenseQuery->get();

        foreach ($expenses as $expense) {
            $dayBookEntries->push([
                'id'          => $expense->id,
                'date'        => $expense->created_at,
                'voucher_no'  => $expense->voucher_no ?? $expense->id,
                'voucher_type' => 'expense',
                'ledger'      => $expense->expense_head ?? 'Expense',
                'debit'       => 0,
                'credit'      => $expense->amount,  // Cash out
                'remarks'     => $expense->note ?? '',
            ]);
        }

        // --------- 3. CASH OUT – PAYMENTS TO SUPPLIERS (optional) ----------
        // If you have a Payment model for party/vendor payments:
        if (class_exists(Payment::class)) {
            $paymentQuery = Payment::query()
                ->whereBetween('created_at', [$from, $to]);

            if ($branchId) {
                $paymentQuery->where('branch_id', $branchId);
            }

            $payments = $paymentQuery->get();

            foreach ($payments as $pay) {
                $dayBookEntries->push([
                    'id'          => $pay->id,
                    'date'        => $pay->created_at,
                    'voucher_no'  => $pay->voucher_no ?? $pay->id,
                    'voucher_type' => 'payment',
                    'ledger'      => $pay->party_name ?? 'Supplier',
                    'debit'       => 0,
                    'credit'      => $pay->amount, // Cash out
                    'remarks'     => $pay->note ?? '',
                ]);
            }
        }

        // --------- SORT BY DATE ----------
        $dayBookEntries = $dayBookEntries->sortBy('date')->values();

        // --------- TOTALS ----------
        $totalDebit  = $dayBookEntries->sum('debit');
        $totalCredit = $dayBookEntries->sum('credit');

        // Opening balance (you can calculate from ledger; here just input or 0)
        $openingBalance = (float) $request->input('opening_balance', 0);

        $closingBalance = $openingBalance + $totalDebit - $totalCredit;

        return view('reports.day_book', [
            'entries'         => $dayBookEntries,
            'fromDate'        => $fromDate,
            'toDate'          => $toDate,
            'branchId'        => $branchId,
            'openingBalance'  => $openingBalance,
            'totalDebit'      => $totalDebit,
            'totalCredit'     => $totalCredit,
            'closingBalance'  => $closingBalance,
        ]);
    }

    public function showVoucher($type, $id)
    {
        switch ($type) {
            case 'invoice':
                $voucher = Invoice::findOrFail($id);
                $view = 'reports.day_book_voucher_invoice';
                $title = 'Invoice #' . ($voucher->invoice_number ?? $voucher->id);
                break;

            case 'expense':
                $voucher = Expense::findOrFail($id);
                $view = 'reports.day_book_voucher_expense';
                $title = 'Expense Voucher #' . ($voucher->voucher_no ?? $voucher->id);
                break;

            // case 'payment':
            //     $voucher = \App\Models\Payment::findOrFail($id);
            //     $view = 'reports.day_book_voucher_payment';
            //     $title = 'Payment Voucher #' . ($voucher->voucher_no ?? $voucher->id);
            //     break;

            default:
                abort(404);
        }

        $html = View::make($view, compact('voucher'))->render();

        return response()->json([
            'title' => $title,
            'html'  => $html,
        ]);
    }
}
