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
}
