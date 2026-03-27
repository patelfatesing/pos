<?php
namespace App\Jobs;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\ShiftCloseMail;


class SendShiftCloseMailJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $shift;
    protected $summary;
    protected $payments;
    protected $departments;
    protected $customers;
    protected $branch_id;

    public function __construct($shift, $summary, $payments, $departments, $customers, $branch_id)
    {
        $this->shift = $shift;
        $this->summary = $summary;
        $this->payments = $payments;
        $this->departments = $departments;
        $this->customers = $customers;
        $this->branch_id = $branch_id;
    }

    public function handle()
    {
        try {
            $branch = Branch::find($this->branch_id);

            // ================= PDF 1 =================
            $shiftFileName = 'shift_' . time() . '.pdf';
            $shiftPdfPath = storage_path('app/public/' . $shiftFileName);

            Pdf::loadView('emails.shift_close_pdf', [
                'shift' => $this->shift,
                'summary' => $this->summary,
                'payments' => $this->payments,
                'departments' => $this->departments,
                'customers' => $this->customers
            ])->save($shiftPdfPath);

            // ================= PDF 2 =================
            $stockFileName = 'stock_' . time() . '.pdf';
            $stockPdfPath = storage_path('app/public/' . $stockFileName);

            $rawStockData = \App\Models\DailyProductStock::where('branch_id', $this->branch_id)
                ->where('shift_id', $this->shift->id)
                ->get();

            Pdf::loadView('emails.stock_summary', [
                'rawStockData' => $rawStockData,
                'shift' => $this->shift,
                'branch_name' => $branch
            ])->setPaper('A4', 'landscape')
                ->save($stockPdfPath);

            // ================= SEND MAIL =================
            $admin = User::find(1);

            Mail::to($admin->email)->send(
                new ShiftCloseMail(
                    $this->shift,
                    $this->summary,
                    $this->payments,
                    $this->departments,
                    $shiftFileName,
                    $shiftPdfPath,
                    $stockFileName,
                    $stockPdfPath
                )
            );

            Log::info('Shift mail sent via queue');
        } catch (\Exception $e) {
            Log::error('Queue Mail Error: ' . $e->getMessage());
        }
    }
}
