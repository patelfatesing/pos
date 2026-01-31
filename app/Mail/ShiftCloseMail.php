<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShiftCloseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $shift;
    public $summary;
    public $payments;
    public $pdfPath;
    public $fileName;
    public $departments;

    public function __construct($shift, $summary, $payments, $departments, $fileName, $pdfPath)
    {
        $this->shift = $shift;
        $this->summary = $summary;
        $this->pdfPath = $pdfPath;
        $this->payments = $payments;
        $this->fileName = $fileName;
        $this->departments = $departments;
    }

    public function build()
    {
        return $this->subject("Shift Closed - " . $this->shift->shift_no)
            ->view('emails.shift-close')
            ->attach($this->pdfPath, [
                'as' => basename($this->pdfPath),
                'mime' => 'application/pdf',
            ]);
    }
}
