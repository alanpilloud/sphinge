<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class CmsversionWordpress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmsversion:wordpress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets the current CMS version of WordPress';

    /**
     * GuzzleHttp Client
     *
     * @var Client
     */
    private $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = new Client([
            'timeout' => 30.0,
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $response = $this->client->request('GET', 'http://api.wordpress.org/core/version-check/1.7/?version=latest');

            if (empty($response->getBody()->getContents())) {
                throw new \Exception("Response body is empty", 1);
            }

            $remote_data = json_decode($response->getBody());

            // cache for 24hours
            Cache::put('current_wp_version', $remote_data->offers[0]->current, 86400);
            echo $remote_data->offers[0]->current.' has been set in the cache for key "current_wp_version".'.PHP_EOL;
        } catch(\GuzzleHttp\Exception\ClientException $e) {
            echo 'error while retrieving data from api.wordpress.org';
        }  catch(\Exception $e) {
            echo 'error while retrieving data from api.wordpress.org';
        }
    }
}
