<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncNotifications extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The notifications
     *
     * @var array
     */
    public $notifications;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('sphinge@bwap.ch')
                    ->subject('Sphinge notifications')
                    ->view('emails.syncNotifications');
    }
}
