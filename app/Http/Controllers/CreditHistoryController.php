<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreditHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use App\Models\Partyuser;


class CreditHistoryController extends Controller
{
    public function index()
    {
        $branches = DB::table('branches')->get();
        return view('credit.credit-ledger', compact('branches'));
    }

    public function creditLedgerData(Request $request)
    {
        $query = DB::table('credit_histories')
            ->join('invoices', 'credit_histories.invoice_id', '=', 'invoices.id')
            ->leftJoin('party_users', 'credit_histories.party_user_id', '=', 'party_users.id')
            ->leftJoin('branches', 'credit_histories.store_id', '=', 'branches.id')
            ->select(
                'credit_histories.id',
                'credit_histories.invoice_id',
                'credit_histories.total_amount',
                'credit_histories.credit_amount',
                'credit_histories.debit_amount',
                'credit_histories.total_purchase_items',
                'credit_histories.type',
                'credit_histories.status',
                'credit_histories.created_at',
                'party_users.first_name as party_user',
                'invoices.invoice_number',
                'branches.name as branch_name'
            );

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('credit_histories.created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        if (!empty($request->branch_id)) {
            $query->where('credit_histories.store_id', $request->branch_id);
        }

        $totalRecords = $query->count();

        if (!empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('invoices.invoice_number', 'like', "%{$searchValue}%")
                    ->orWhere('party_users.first_name', 'like', "%{$searchValue}%")
                    ->orWhere('branches.name', 'like', "%{$searchValue}%")
                    ->orWhere('credit_histories.type', 'like', "%{$searchValue}%")
                    ->orWhere('credit_histories.status', 'like', "%{$searchValue}%");
            });
        }

        $filteredRecords = $query->count();

        $columns = [
            'credit_histories.id',
            'invoices.invoice_number',
            'party_user',
            'credit_histories.total_amount',
            'credit_histories.credit_amount',
            'credit_histories.debit_amount',
            'credit_histories.total_purchase_items',
            'credit_histories.type',
            'branches.name',
            'credit_histories.status',
            'credit_histories.created_at',
        ];

        $orderColumn = $columns[$request->order[0]['column']] ?? 'credit_histories.created_at';
        $orderDir = $request->order[0]['dir'] ?? 'desc';
        $query->orderBy($orderColumn, $orderDir);

        if ($request->length > 0) {
            $query->skip($request->start)->take($request->length);
        }

        $records = $query->get();

        $data = [];
        foreach ($records as $record) {
            $action = '<a href="' . url('/view-invoice/' . $record->invoice_id) . '" class="badge badge-info">' . $record->invoice_number . '</a>';

            $data[] = [
                'invoice_number' => $action,
                'party_user' => $record->party_user ?? 'N/A',
                'type' => ucfirst($record->type),
                'total_amount' => number_format($record->total_amount, 2),
                'credit_amount' => number_format($record->credit_amount, 2),
                'debit_amount' => number_format($record->debit_amount, 2),
                'total_purchase_items' => $record->total_purchase_items,
                'branch_name' => $record->branch_name,
                'status' => $record->status,
                'created_at' => Carbon::parse($record->created_at)->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function downloadLedgerPdf(Request $request, $partyUserId)
    {
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $user = Partyuser::findOrFail($partyUserId);

        $ledgerEntries = CreditHistory::where('party_user_id', $partyUserId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $openingBalance = $user->opening_balance ?? 0;
        $closingBalance = $openingBalance;
        $transactions = [];
        $totalCredit = 0;
        $totalDebit = 0;

        foreach ($ledgerEntries as $entry) {
            $credit = $entry->credit_amount ?? 0;
            $debit = $entry->debit_amount ?? 0;

            $closingBalance += $debit - $credit;
            $totalCredit += $credit;
            $totalDebit += $debit;

            $transactions[] = [
                'date' => Carbon::parse($entry->created_at)->format('d/m/Y H:i'),
                'epos' => $entry->store_id ?? '-',
                'ref_number' => $entry->invoice_id ?? '-',
                'type' => ucfirst($entry->type),
                'credit' => $credit,
                'debit' => $debit,
                'closing' => $closingBalance,
            ];
        }

        $pdf = PDF::loadView('pdfs.detailed-ledger', [
            'user' => $user,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $openingBalance,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'netOutstanding' => $closingBalance,
            'transactions' => $transactions,
        ]);

        return $pdf->download('credit_ledger.pdf');
    }
}
