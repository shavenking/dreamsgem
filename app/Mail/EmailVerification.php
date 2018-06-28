<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailVerification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $emailVerification;

    /**
     * Create a new message instance.
     *
     * @param User $user
     */
    public function __construct(User $user, \App\EmailVerification $emailVerification)
    {
        $this->subject('夢寶龍帳號啟用');
        $this->user = $user;
        $this->emailVerification = $emailVerification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'))->markdown('emails.email-verification');
    }
}
