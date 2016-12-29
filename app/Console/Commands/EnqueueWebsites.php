<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Website;
use \App\Jobs\SyncWebsite;

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
        }

    }
}
