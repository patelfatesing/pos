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

    public function __construct($shift, $summary)
    {
        $this->shift = $shift;
        $this->summary = $summary;
    }

    public function build()
    {
        return $this->subject("Shift Closed - " . $this->shift->shift_no)
                    ->view('emails.shift-close');
    }
}
