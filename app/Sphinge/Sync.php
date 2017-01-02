<?php

namespace App\Sphinge;

use GuzzleHttp\Client;
use App\Notifications\SyncAlert;
use Illuminate\Support\Facades\Auth;
use \App\User;
use Ramsey\Uuid\Uuid;

class Sync {

    /**
     * Website object
     *
     * @var Website
     */
    private $website;

    /**
     * Array of Extensions
     *
     * @var array
     */
    private $extensions;

    /**
     * GuzzleHttp Client
     *
     * @var Client
     */
    private $client;

    /**
     * Json Object from the webservice
     *
     * @var stdClass Object
     */
    private $jsonResponse;

    /**
     * Fields to be fetched and compared
     *
     * @var array
     */
    private $websiteFields = [
        'sphinge_version',
        'wp_version',
        'php_version',
        'mysql_version',
    ];

    /**
     * Describes the context in which the Sync has been initiated
     *
     * @var string  'cron' or 'manual'
     */
    private $context;

    public function __construct(\App\Website $website, array $extensions, $context = 'cron')
    {
        $this->website = $website;
        $this->extensions = $extensions;
        $this->client = new Client([
            'base_uri' => $website->url,
            'timeout'  => 5.0,
            'headers' => [
                'MONITORING-AGENT' => 'sphinge-monitoring'
            ],
            'query' => [
                'key' => $this->website->secret_key
            ]
        ]);
        $this->context = $context;
        $this->user = Auth::user() ?: User::find($website->user_id);
    }

    /**
     * Get the remote data
     *
     * @return void
     */
    public function fetch()
    {
        try {
            $response = $this->client->request('GET', '/sphinge/report.php');
            $this->jsonResponse = json_decode($response->getBody());
        } catch(\GuzzleHttp\Exception\ClientException $e) {
            $alert = [
                'context' => $this->context,
                'website_name' => $this->website->name,
                'message' => 'Can\'t reach remote website.',
                'status' => 'danger'
            ];
            $this->user->notify(new SyncAlert($alert));
        }
    }

    /**
     * Update a website's informations
     *
     * @return void
     */
    public function updateWebsite()
    {
        if (empty($this->jsonResponse)) {
            return false;
        }

        // compare new and old values
        $this->compareWebsite();

        // Assign new values to fields
        foreach ($this->websiteFields as $field) {
            $this->website->{$field} = $this->jsonResponse->system->{$field};
        }

        $this->website->save();
    }

    /**
     * Update a website's extensions informations
     *
     * @return void
     */
    public function updateExtensions()
    {
        if (empty($this->jsonResponse)) {
            return false;
        }

        // compare new and old extensions values
        $this->compareExtensions();

        //Delete all extensions
        \App\Extension::where('website_id', $this->website->id)->delete();

        foreach ($this->jsonResponse->extensions as $remoteExtension) {
            $extension = new \App\Extension;
            $extension->id = Uuid::uuid4()->toString();
            $extension->name = $remoteExtension->Name;
            $extension->type = $remoteExtension->Type;
            $extension->version = $remoteExtension->Version;
            $extension->website_id = $this->website->id;
            $extension->save();
        }
    }

    /**
     * Compare new and old website's informations
     *
     * @return void
     */
    private function compareWebsite()
    {
        if (empty($this->jsonResponse)) {
            return false;
        }

        foreach ($this->websiteFields as $field) {
            // if the value has changed, notify the user
            if ($this->website->{$field} != $this->jsonResponse->system->{$field}) {
                $alert = [
                    'context' => $this->context,
                    'website_name' => $this->website->name,
                    'message' => $field.'\'s version has change from '.$this->website->{$field}.' to '.$this->jsonResponse->system->{$field},
                    'status' => 'warning'
                ];
                $this->user->notify(new SyncAlert($alert));
            }
        }

    }

    /**
     * Compare new and old website's extensions informations
     *
     * @return void
     */
    private function compareExtensions()
    {
        if (empty($this->jsonResponse)) {
            return false;
        }

        $extensions = \App\Extension::where('website_id', $this->website->id)->get();
        $remoteExtensions = $this->jsonResponse->extensions;

        // Check for version changes
        if (!empty($extensions)) {
            foreach ($extensions as $j => $extension) {
                foreach ($remoteExtensions as $k => $remoteExtension) {
                    if ($extension->name == $remoteExtension->Name) {
                        // If the version has changed, notify the user
                        if ($extension->version != $remoteExtension->Version) {
                            $alert = [
                                'context' => $this->context,
                                'website_name' => $this->website->name,
                                'message' => $extension->name.'\'s ('.$extension->type.') version has change from '.$extension->version.' to '.$remoteExtension->Version,
                                'status' => 'warning'
                            ];
                            $this->user->notify(new SyncAlert($alert));
                        }

                        // delete this found extension from the array
                        unset($extensions[$j]);
                        unset($remoteExtensions[$k]);

                        // as we have found the corresponding extensions, no need to search anymore
                        break;
                    }
                }
            }
        }

        // Check if there are some deleted extensions
        if (!empty($extensions)) {
            foreach ($extensions as $extension) {
                $alert = [
                    'context' => $this->context,
                    'website_name' => $this->website->name,
                    'message' => $extension->name.' ('.$extension->type.') has been deleted',
                    'status' => 'warning'
                ];
                $this->user->notify(new SyncAlert($alert));
            }
        }

        // Check if there are some new extensions
        if (!empty($remoteExtensions)) {
            foreach ($remoteExtensions as $remoteExtension) {
                $alert = [
                    'context' => $this->context,
                    'website_name' => $this->website->name,
                    'message' => $remoteExtension->Name.' ('.$remoteExtension->Type.') version '.$remoteExtension->Version.' has been installed',
                    'status' => 'warning'
                ];
                $this->user->notify(new SyncAlert($alert));
            }
        }

    }
}
