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
}
