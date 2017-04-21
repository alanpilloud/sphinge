<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InterceptorLog extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
}
