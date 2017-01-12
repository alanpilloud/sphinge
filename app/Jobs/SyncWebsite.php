<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncWebsite implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Website Eloquent model
     *
     * @var \App\Website
     */
    protected $website;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Website $website)
    {
        $this->website = $website;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $extensions = $this->website->extentions ?: [];

        $sync = new \App\Sphinge\Sync($this->website, $extensions, 'cron');
        $sync->fetch();
        $sync->updateWebsite();
        $sync->updateExtensions();
        $sync->updateUsers();
    }
}
