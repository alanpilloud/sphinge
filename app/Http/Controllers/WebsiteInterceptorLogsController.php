<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Website;

class WebsiteInterceptorLogsController extends Controller
{
    /**
     * Display a listing of logs for a given website.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id) 
    {
        $website = Website::findOrFail($id);
        return view('website.logs', ['website' => $website, 'logs' => $website->interceptor_logs]);
    }
}
