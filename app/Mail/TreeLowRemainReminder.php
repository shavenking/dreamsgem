<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TreeLowRemainReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $remain;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($remain)
    {
        $this->subject('夢寶石數量過低警告');
        $this->remain = $remain;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.tree-low-remain-reminder');
    }
}
