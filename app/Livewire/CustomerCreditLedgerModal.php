<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB; // ✅ Correct import
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Branch;

class CustomerCreditLedgerModal extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    public $search = '';
    public $startDate;
    public $endDate;
    public $branch_id;


    public function openModal()
    {
        $this->showModal = true;

        // Dispatch browser event to show modal
        $this->dispatch('show-customer-credit-ledger-modal');
    }

    public function downloadPDF()
    {
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $branch = Branch::find($branch_id);
        $query = DB::table('credit_histories')
            ->leftJoin('invoices', 'credit_histories.invoice_id', '=', 'invoices.id')
            ->leftJoin('party_users', 'credit_histories.party_user_id', '=', 'party_users.id')
            ->leftJoin('branches', 'credit_histories.store_id', '=', 'branches.id')
            ->select(
                'credit_histories.*',
                'party_users.first_name as party_user',
                'invoices.invoice_number',
                'branches.name as branch_name'
            );

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('credit_histories.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        if ($this->search) {
            $query->where(function ($subQuery) {
                $subQuery->where('party_users.first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('invoices.invoice_number', 'like', '%' . $this->search . '%');
            });
        }

        $ledgers = $query->orderByDesc('credit_histories.created_at')->get();

        $totalCredit = $ledgers->sum('credit_amount');
        $totalDebit = $ledgers->sum('debit_amount');

        $netOutstanding = $totalDebit - $totalCredit;

        $pdf = Pdf::loadView('pdfs.credit-ledger-list', [
            'ledgers' => $ledgers,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'search' => $this->search,
            'branchName' => $branch->name ?? null,
            'branchAddress' => $branch->address ?? null,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'netOutstanding' => $netOutstanding
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'customer_ledger_' . now()->format('Ymd_His') . '.pdf');
    }

    public function render()
    {
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $query = DB::table('credit_histories')
            ->leftJoin('invoices', 'credit_histories.invoice_id', '=', 'invoices.id')
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


        if ($this->startDate && $this->endDate) {
            $query->whereBetween('credit_histories.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        if (!empty($branch_id)) {
            $query->where('credit_histories.store_id', $branch_id);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('invoices.invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhere('party_users.first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('branches.name', 'like', '%' . $this->search . '%')
                    ->orWhere('credit_histories.type', 'like', '%' . $this->search . '%')
                    ->orWhere('credit_histories.status', 'like', '%' . $this->search . '%');
            });
        }

        $records = $query->orderBy('credit_histories.created_at', 'desc')->paginate(10);

        $totalCredit = $records->sum('credit_amount');
        $totalDebit = $records->sum('debit_amount');
        // $openingBalance = $user->opening_balance ?? 0;
        $netOutstanding = $totalDebit - $totalCredit;

        return view('livewire.customer-credit-ledger-modal', [
            'creditLedgers' => $records,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'netOutstanding' =>$netOutstanding
        ]);
    }
}
