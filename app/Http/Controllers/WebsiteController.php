<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Website;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WebsiteController extends Controller
{
    /**
     * Display a listing of websites.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('website.index', ['websites' => Auth::user()->websites]);
    }

    /**
     * Show the form for creating a new website.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('website.create');
    }

    /**
     * Store a newly created website in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $website = new Website();
        $website->name = $request->name;
        $website->url = $request->url;
        $website->secret_key = $request->secret_key;
        $website->user_id = Auth::id();
        $website->save();
        return redirect()->action('WebsiteController@index');
    }

    /**
     * Display the specified website.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $website = Website::findOrFail($id);
        return view('website.detail', ['website' => $website, 'extensions' => $website->extensions]);
    }

    /**
     * Show the form for editing the specified website.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $website = Website::findOrFail($id);
        return view('website.edit', ['website' => $website]);
    }

    /**
     * Update the specified website in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $website = Website::findOrFail($id);
        $website->name = $request->name;
        $website->url = $request->url;
        $website->secret_key = $request->secret_key;
        $website->save();
        return redirect()->action('WebsiteController@index');
    }

    /**
     * Remove the specified website from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Website::destroy($id);
        return redirect()->action('WebsiteController@index')->with('notifications', [['message' => 'Deleted succesfully', 'status' => 'success']]);
    }

    /**
     * Synchronize the website with it's remote content
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sync($id)
    {
        $website = Website::findOrFail($id);
        $extensions = $website->extentions ?: [];
        $sync = new \App\Sphinge\Sync($website, $extensions, 'manual');
        $sync->fetch();
        $sync->updateWebsite();
        $sync->updateExtensions();

        // get the notifications
        $notificationsQuery = DB::table('notifications')
                ->where([
                    ['notifiable_id', Auth::user()->id],
                    ['data->context', 'manual']
                ]);

        $notifications = $notificationsQuery->get();
        $notificationsQuery->delete();

        // get notifications from the array to send them to the blade template
        $notificationsArray = array_map('json_decode', array_pluck($notifications, 'data'));

        return redirect()->action('WebsiteController@show', ['id' => $id])->with('notifications', $notificationsArray);
    }

    /**
     * Performs a security audit
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function audit($id) {
        $website = Website::findOrFail($id);
        $audit = new \App\Sphinge\Audit($website);
        $audit->run();
        return view('website.audit', ['website' => $website, 'rules' => $audit->rules]);
    }
}
