<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    /**
     * Get the extensions for the website.
     */
    public function extensions()
    {
        return $this->hasMany('App\Extension');
    }

    /**
     * Get the websites users for the website.
     */
    public function website_users()
    {
        return $this->hasMany('App\WebsiteUser');
    }

    /**
     * Get the interceptor logs for the website.
     */
    public function interceptor_logs()
    {
        return $this->hasMany('App\InterceptorLog');
    }

    /**
     * Get the scores for the website.
     */
    public function scores()
    {
        return $this->hasMany('App\Score');
    }

    /**
     * Check if the website has an extension
     */
    public function hasExtension($extension_name)
    {
        return in_array($extension_name, $this->extensions->pluck('name')->all());
    }
}
