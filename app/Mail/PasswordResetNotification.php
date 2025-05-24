<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $newPassword;
    public $email;

    public function __construct($newPassword,$email)
    {
        $this->newPassword = $newPassword;
        $this->email = $email;

    }

    public function build()
    {
        return $this->subject('Your New Password')
                    ->view('emails.password_reset')
                    ->with([
                        'newPassword' => $this->newPassword,
                        'email' => $this->email,

                    ]);
    }
}
