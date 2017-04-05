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
        $this->client = new Client([
            'base_uri' => $website->url,
            'timeout'  => 30.0,
            'headers' => [
                'MONITORING-AGENT' => 'sphinge-monitoring'
            ],
            'query' => [
                'key' => $this->website->secret_key
            ]
        ]);


        /**
         * Now, run the synchronization
         */
        $this->reportResponse = $this->fetch('/sphinge/report.php');
        $this->jsonResponse = json_decode($this->reportResponse->getBody());
        $this->homepageResponse = $this->fetch('/');
        $this->updateWebsite();
        $this->updateExtensions();
        $this->updateUsers();
    }

    /**
     * Get the remote data
     *
     * @var string  url path without domain name, begins with slash
     *
     * @return GuzzleHttp Response
     */
    public function fetch($path)
    {
        try {
            $response = $this->client->request('GET', $path);

            if (empty($response->getBody()->getContents())) {
                throw new \Exception("Response body is empty", 1);
            }
        } catch(\GuzzleHttp\Exception\ClientException $e) {
            $alert = [
                'context' => $this->context,
                'website_name' => $this->website->name,
                'message' => 'Can\'t reach '.$this->website->url.$path,
                'status' => 'danger'
            ];
            $this->user->notify(new SyncAlert($alert));
        }  catch(\Exception $e) {
            $alert = [
                'context' => $this->context,
                'website_name' => $this->website->name,
                'message' => 'Can\'t reach '.$this->website->url.$path.'. Please, check that the secret key has been correctly provided.',
                'status' => 'danger'
            ];
            $this->user->notify(new SyncAlert($alert));
        }

        return $response;
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
        // compare homepage size
        $this->compareHomepageLength();

        // Assign new values to fields
        foreach ($this->websiteFields as $field) {
            $this->website->{$field} = $this->jsonResponse->system->{$field};
        }

        // assign new homepage length
        $this->website->homepage_length = strlen($this->homepageResponse->getBody()) or 0;

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
            }
        }
    }

    /**
     * Compare homepage's content length
     *
     * @return void
     */
    private function compareHomepageLength()
    {
        $remote_homepage_length = strlen($this->homepageResponse->getBody()) or 0;
        $homepage_length_delta = abs($this->website->homepage_length - $remote_homepage_length);

        if ($homepage_length_delta > 50) {
            $alert = [
                'context' => $this->context,
                'website_name' => $this->website->name,
                'message' => 'Homepage\'s content length has a change of '.$homepage_length_delta.' characters.',
                'status' => 'warning'
            ];
            $this->user->notify(new SyncAlert($alert));
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
                        }

                        // delete this found extension from the array
                        unset($users[$j]);
                        unset($remoteUsers[$k]);

                        // as we have found the corresponding extensions, no need to search anymore
                        break;
                    }
                }
            }
        }

        // Check if there are some deleted users
        if (!empty($users)) {
            foreach ($users as $user) {
                $alert = [
                    'context' => $this->context,
                    'website_name' => $this->website->name,
                    'message' => 'User '.$user->login.' has been deleted',
                    'status' => 'warning'
                ];
                $this->user->notify(new SyncAlert($alert));
            }
        }

        // Check if there are some new users
        if (!empty($remoteUsers)) {
            foreach ($remoteUsers as $remoteUser) {
                $alert = [
                    'context' => $this->context,
                    'website_name' => $this->website->name,
                    'message' => 'User '.$remoteUser->user_login.' has been added',
                    'status' => 'warning'
                ];
                $this->user->notify(new SyncAlert($alert));
            }
        }

    }
}
