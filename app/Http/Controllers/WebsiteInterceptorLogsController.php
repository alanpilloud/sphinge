<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Website;
use App\InterceptorLog;

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

    /**
     * Remove all logs from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroyAll($id)
    {
        InterceptorLog::where('website_id', $id)->delete();

        $notification = new \stdClass();
        $notification->message = 'Deleted succesfully';
        $notification->status = 'success';

        return redirect()->action('WebsiteInterceptorLogsController@index', [$id])->with('notifications', [$notification]);
    }
}
