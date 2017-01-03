<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Website;
use \App\Jobs\SyncWebsite;
use \App\User;
use \App\Mail\SyncNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EnqueueWebsites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websites:enqueue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all websites by sending them to the queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $websites = Website::all();
        if (!empty($websites)) {
            // add each website in queue
            foreach ($websites as $website) {
                dispatch(new SyncWebsite($website));
                $this->info($website->name.' enqueued.');
            }

            // get the notifications
            // @todo the LIKE needs to be replaced when mysql 5.7 will be more globally supported
            $notificationsQuery = DB::table('notifications')->where('data', 'like', '%"context":"cron"%');

            $notifications = $notificationsQuery->get();
            $notificationsQuery->delete();

            if (!empty($notifications)) {
                $notificationsByUser = [];
                // get the notifications sorted by user ids
                foreach ($notifications as $notification) {
                    $notificationsByUser[$notification->notifiable_id][] = json_decode($notification->data);
                }

                foreach ($notificationsByUser as $user_id => $notifications) {
                    $User = User::find($user_id);

                    Mail::to($User)->send(new SyncNotifications($notifications));
                }
            }
        }

    }
}
