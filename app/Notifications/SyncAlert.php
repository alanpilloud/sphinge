<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncAlert extends Notification
{
    use Queueable;

    /**
     * Alert message to be notified
     *
     * @var array
     */
    private $alert;

    /**
     * Create a new notification instance.
     *
     * @param array $alert [description]
     * @return void
     */
    public function __construct(array $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'context' => $this->alert['context'],
            'website_name' => $this->alert['website_name'],
            'message' => $this->alert['message'],
            'status' => $this->alert['status']
        ];
    }
}
