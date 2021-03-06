<?php

namespace App\Sphinge;

use GuzzleHttp\Client;
use App\Notifications\SyncAlert;
use Illuminate\Support\Facades\Auth;
use \App\User;
use \App\Score;
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
     * GuzzleHttp Response for the report request
     *
     * @var Response
     */
    private $reportResponse;

    /**
     * Json Object from the webservice
     *
     * @var stdClass Object
     */
    private $jsonResponse;

    /**
     * GuzzleHttp Response for the homepage request
     *
     * @var Response
     */
    private $homepageResponse;

    /**
     * Fields to be fetched and compared
     *
     * @var array
     */
    private $websiteFields = [
        'wp_version',
        'php_version',
        'mysql_version',
    ];

    /**
     * Guzzle client defaults values
     *
     * @var array
     */
    private $client_defaults = [];

    /**
     * Describes the context in which the Sync has been initiated
     *
     * @var string  'cron' or 'manual'
     */
    private $context;

    public function __construct(\App\Website $website, array $extensions, $context = 'cron')
    {
        $this->user = Auth::user() ?: User::find($website->user_id);
        $this->website = $website;
        $this->context = $context;

        if (empty($this->website->secret_key)) {
            $alert = [
                'context' => $this->context,
                'website_name' => $this->website->name,
                'message' => 'Please indicate a secret key',
                'status' => 'warning'
            ];
            $this->user->notify(new SyncAlert($alert));
            return false;
        }

        $this->extensions = $extensions;

        $this->client_defaults = [
            'base_uri' => rtrim($website->url, '/').'/', // make sure that the url ends with a /
            'timeout'  => 30.0,
            'verify' => false,
            'headers' => [
                'MONITORING-AGENT' => 'sphinge-monitoring',
                'SPHINGE-KEY' => $this->website->secret_key
            ],
        ];


        /**
         * Now, run the synchronization
         */
        $this->reportResponse = $this->fetch('get_report');
        if ($this->reportResponse !== false) {
            $this->jsonResponse = json_decode($this->reportResponse->getBody());
            $this->homepageResponse = $this->fetch('get_homepage');
            $this->updateWebsite();
            $this->updateExtensions();
            $this->updateUsers();
        }
    }

    /**
     * Get the remote data
     *
     * @var string  action to execute on the remote website
     *
     * @return GuzzleHttp Response
     */
    public function fetch($action)
    {
        if (empty($action)) {
            return false;
        }
        
        // clone the default client configuration and assign the action in headers
        $request_params = $this->client_defaults;
        $request_params['headers']['SPHINGE-ACTION'] = $action;

        $this->client = new Client($request_params);

        try {
            $response = $this->client->request('GET');

            if (empty($response->getBody()->getContents())) {
                throw new \Exception("Response body is empty", 1);
            }

            return $response;
        } catch(\GuzzleHttp\Exception\ClientException $e) {
            $alert = [
                'context' => $this->context,
                'website_name' => $this->website->name,
                'message' => 'Can\'t reach '.$this->website->url,
                'status' => 'danger'
            ];
            $this->user->notify(new SyncAlert($alert));
            
            return false;
        }  catch(\Exception $e) {
            $alert = [
                'context' => $this->context,
                'website_name' => $this->website->name,
                'message' => 'Can\'t reach '.$this->website->url.'. Please, check that the secret key has been correctly provided.'.$e->getMessage(),
                'status' => 'danger'
            ];
            $this->user->notify(new SyncAlert($alert));

            return false;
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
            $extension->new_version = $remoteExtension->New_version ?? null;
            $extension->website_id = $this->website->id;
            $extension->save();
        }
    }

    /**
     * Update a website's users informations
     *
     * @return void
     */
    public function updateUsers()
    {
        if (empty($this->jsonResponse->users)) {
            return false;
        }

        // compare new and old extensions values
        $this->compareUsers();

        //Delete all users
        \App\WebsiteUser::where('website_id', $this->website->id)->delete();

        foreach ($this->jsonResponse->users as $remoteUser) {
            $user = new \App\WebsiteUser;
            $user->id = Uuid::uuid4()->toString();
            $user->remote_id = $remoteUser->id;
            $user->login = $remoteUser->user_login;
            $user->registered = $remoteUser->user_registered;
            $user->email = $remoteUser->user_email;
            $user->website_id = $this->website->id;

            $user->save();
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

                // when a value changes, update its related score
                Score::up($field, $this->website->id);
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
        if (empty($this->jsonResponse->extensions)) {
            return false;
        }

        $extensions = \App\Extension::where('website_id', $this->website->id)->get();
        $remoteExtensions = $this->jsonResponse->extensions;

        // Check for version changes
        if (!empty($extensions)) {
            $scoreCounter = 0;
            foreach ($extensions as $j => $extension) {
                foreach ($remoteExtensions as $k => $remoteExtension) {
                    if ($extension->name == $remoteExtension->Name) {
                        // If the version has changed, notify the user
                        if ($extension->version != $remoteExtension->Version) {
                            $alert = [
                                'context' => $this->context,
                                'website_name' => $this->website->name,
                                'message' => $extension->name.'\'s ('.$extension->type.') version has changed from '.$extension->version.' to '.$remoteExtension->Version,
                                'status' => 'warning'
                            ];
                            $this->user->notify(new SyncAlert($alert));

                            $scoreCounter++;
                        }

                        // delete this found extension from the array
                        unset($extensions[$j]);
                        unset($remoteExtensions[$k]);

                        // as we have found the corresponding extensions, no need to search anymore
                        break;
                    }
                }
            }

            // update the number of extensions updated
            Score::up('extensions_updated', $this->website->id, $scoreCounter);
        }

        // Check if there are some deleted extensions
        if (!empty($extensions)) {
            $scoreCounter = 0;
            foreach ($extensions as $extension) {
                $alert = [
                    'context' => $this->context,
                    'website_name' => $this->website->name,
                    'message' => $extension->name.' ('.$extension->type.') has been deleted',
                    'status' => 'warning'
                ];
                $this->user->notify(new SyncAlert($alert));
            }

            // update the number of extensions that have been deleted
            Score::up('extensions_deleted', $this->website->id, $scoreCounter);
        }

        // Check if there are some new extensions
        if (!empty($remoteExtensions)) {
            $scoreCounter = 0;
            foreach ($remoteExtensions as $remoteExtension) {
                $alert = [
                    'context' => $this->context,
                    'website_name' => $this->website->name,
                    'message' => $remoteExtension->Name.' ('.$remoteExtension->Type.') version '.$remoteExtension->Version.' has been installed',
                    'status' => 'warning'
                ];
                $this->user->notify(new SyncAlert($alert));
            }

            // update the number of extensions that have been installed
            Score::up('extensions_installed', $this->website->id, $scoreCounter);
        }

    }

    /**
     * Compare new and old website's users informations
     *
     * @return void
     */
    private function compareUsers()
    {
        if (empty($this->jsonResponse)) {
            return false;
        }

        $users = \App\WebsiteUser::where('website_id', $this->website->id)->get();
        $remoteUsers = $this->jsonResponse->users;

        // Check for version changes
        if (!empty($users)) {
            $scoreCounter = 0;

            foreach ($users as $j => $user) {
                foreach ($remoteUsers as $k => $remoteUser) {
                    if ($user->remote_id == $remoteUser->id) {
                        // If the email has changed, notify the user
                        if ($user->email != $remoteUser->user_email) {
                            $alert = [
                                'context' => $this->context,
                                'website_name' => $this->website->name,
                                'message' => $user->email.'\'s email has changed from '.$user->email.' to '.$remoteUser->user_email,
                                'status' => 'warning'
                            ];
                            $this->user->notify(new SyncAlert($alert));

                            $scoreCounter++;
                        }

                        // If the login has changed, notify the user
                        if ($user->login != $remoteUser->user_login) {
                            $alert = [
                                'context' => $this->context,
                                'website_name' => $this->website->name,
                                'message' => $user->login.'\'s login has changed from '.$user->login.' to '.$remoteUser->user_login,
                                'status' => 'warning'
                            ];
                            $this->user->notify(new SyncAlert($alert));

                            $scoreCounter++;
                        }

                        // delete this found extension from the array
                        unset($users[$j]);
                        unset($remoteUsers[$k]);

                        // as we have found the corresponding extensions, no need to search anymore
                        break;
                    }
                }
            }

            // update the number of users that have been updated
            Score::up('users_updated', $this->website->id, $scoreCounter);
        }

        // Check if there are some deleted users
        if (!empty($users)) {

            $scoreCounter = 0;
            foreach ($users as $user) {
                $alert = [
                    'context' => $this->context,
                    'website_name' => $this->website->name,
                    'message' => 'User '.$user->login.' has been deleted',
                    'status' => 'warning'
                ];
                $this->user->notify(new SyncAlert($alert));

                $scoreCounter++;
            }

            // update the number of users that have been deleted
            Score::up('users_deleted', $this->website->id, $scoreCounter);
        }

        // Check if there are some new users
        if (!empty($remoteUsers)) {

            $scoreCounter = 0;
            foreach ($remoteUsers as $remoteUser) {
                $alert = [
                    'context' => $this->context,
                    'website_name' => $this->website->name,
                    'message' => 'User '.$remoteUser->user_login.' has been added',
                    'status' => 'warning'
                ];
                $this->user->notify(new SyncAlert($alert));

                $scoreCounter++;
            }

            // update the number of users that have been created
            Score::up('users_created', $this->website->id, $scoreCounter);
        }

    }
}
