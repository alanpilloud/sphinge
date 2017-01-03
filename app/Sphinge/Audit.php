<?php

namespace App\Sphinge;

use GuzzleHttp\Client;
use \App\Sphinge\AuditRule;

class Audit {

    /**
     * Website object
     *
     * @var Website
     */
    private $website;

    /**
     * Rules that have been tested
     *
     * @var array
     */
    public $rules = [];

    public function __construct(\App\Website $website)
    {
        $this->website = $website;
        $this->client = new Client([
            'base_uri' => $website->url,
            'timeout'  => 5.0
        ]);
    }

    /**
     * Get the remote data and run the tests
     *
     * @return void
     */
    public function run()
    {
        try {
            $this->response = $this->client->request('GET', '/');

            $this->rules[] = new AuditRule(
                'Set X-Frame-Options header',
                'By adding a response header "X-Frame-Options: DENY", you ensure that your website can\'t be iframed.',
                in_array('DENY', $this->response->getHeader('X-Frame-Options'))
            );

            $this->rules[] = new AuditRule(
                'Set X-Content-Type-Options header',
                'By adding a response header "X-Content-Type-Options: nosniff", you ensure that browsers don\'t try to render anything else than the specified mime type.',
                in_array('nosniff', $this->response->getHeader('X-Content-Type-Options'))
            );

            $this->rules[] = new AuditRule(
                'Unset Server header',
                'By removing the response header "Server", you gain security by obfuscating sensitive informations.',
                !$this->response->hasHeader('Server')
            );

            $this->rules[] = new AuditRule(
                'Unset X-Powered-By header',
                'By removing the response header "X-Powered-By", you gain security by obfuscating sensitive informations.',
                !$this->response->hasHeader('X-Powered-By')
            );

        } catch(\GuzzleHttp\Exception\ClientException $e) {
            // send message to user, we cant reach the website
        }
    }

}
