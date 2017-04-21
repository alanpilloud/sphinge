<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\InterceptorLog;
use App\Website;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class InterceptorLogController extends Controller
{
    /**
     * Display a listing of the interceptor log.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $logs = InterceptorLog::all();
        print_r($logs);
    }

    /**
     * Store a newly created interceptor log in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $message = $request->json()->all();
        $messageHash = md5(json_encode($message));
        $website = Website::where('secret_key', $message['website_secret_key'])->first();

        // try to find if the error has already been recorded
        $log = InterceptorLog::where([
            ['hash', $messageHash],
            ['website_id', $website->id]
        ])->first();

        if (!empty($log)) {
            // if there is already an entry for this message,
            // increment the occurences counter
            $log->occurences += 1;
        } else {
            // if there is no entry for this message,
            // create a new one
            $log = new InterceptorLog();
            $log->id = Uuid::uuid4()->toString();
            $log->hash = $messageHash;
            $log->type = $message['type'];
            $log->message = $message['message'];
            $log->file = $message['file'];
            $log->line = $message['line'];
            $log->website_id = $website->id;
            $log->occurences = 1;
        }

        $log->last_occurence = \Carbon\Carbon::now()->toDateTimeString();
        $log->save();

        // no need to return anything, no one's listening
        exit();
    }

    /**
     * Display the specified interceptor log.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $log = InterceptorLog::findOrFail($id);
        $website = Website::findOrFail($log->website_id);
        return view('log.detail', ['website' => $website, 'log' => $log]);
    }

    /**
     * Show the form for editing the specified interceptor log.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified interceptor log in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified interceptor log from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
