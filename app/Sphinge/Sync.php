<?php

namespace App\Sphinge;

use GuzzleHttp\Client;

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
     * Holds all notifications sent by the class
     *
     * @var array
     */
    public $notifications;

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

    public function __construct(\App\Website $website, array $extensions)
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
            $this->notifications[] = ['message' => 'Can\'t reach remote website.', 'status' => 'danger'];
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
                $this->notifications[] = ['message' => $field.'\'s version has change from '.$this->website->{$field}.' to '.$this->jsonResponse->system->{$field}, 'status' => 'warning'];
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
                            $this->notifications[] = ['message' => $extension->name.'\'s ('.$extension->type.') version has change from '.$extension->version.' to '.$remoteExtension->Version, 'status' => 'warning'];
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
                $this->notifications[] = ['message' => $extension->name.' ('.$extension->type.') has been deleted', 'status' => 'warning'];
            }
        }

        // Check if there are some new extensions
        if (!empty($remoteExtensions)) {
            foreach ($remoteExtensions as $remoteExtension) {
                $this->notifications[] = ['message' => $remoteExtension->Name.' ('.$remoteExtension->Type.') version '.$remoteExtension->Version.' has been installed', 'status' => 'warning'];
            }
        }

    }
}
