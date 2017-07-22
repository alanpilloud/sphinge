<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Website;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

class WebsiteController extends Controller
{
    /**
     * Display a listing of websites.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $current_wp_version = Cache::get('current_wp_version', '0');

        return view('website.index', [
            'current_wp_version' => $current_wp_version,
            'websites' => Auth::user()->websites
        ]);
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
        $website->secret_key = Uuid::uuid4()->toString();
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
        $sphinge_extension = array_first($website->extensions, function($value, $key) {
            return $value->name == 'Sphinge';
        });
        return view('website.detail', [
            'website' => $website,
            'sphinge_version' => $sphinge_extension['version'],
            'extensions' => $website->extensions,
            'users' => $website->website_users
        ]);
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

        $notification = new \stdClass();
        $notification->message = 'Deleted succesfully';
        $notification->status = 'success';

        return redirect()->action('WebsiteController@index')->with('notifications', [$notification]);
    }

    /**
     * Synchronize the website with its remote content
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sync($id)
    {
        $website = Website::findOrFail($id);
        $extensions = $website->extentions ?: [];
        $sync = new \App\Sphinge\Sync($website, $extensions, 'manual');

        // get the notifications
        // @todo the LIKE needs to be replaced when mysql 5.7 will be more globally supported
        $notificationsQuery = DB::table('notifications')
                ->where([
                    ['notifiable_id', Auth::user()->id],
                    ['data', 'like', '%"context":"manual"%']
                ]);

        $notifications = $notificationsQuery->get();
        $notificationsQuery->delete();

        // get notifications from the array to send them to the blade template
        $notificationsArray = array_map('json_decode', array_pluck($notifications, 'data'));

        // if there are no notifications, just send a success message
        if (empty($notificationsArray)) {
            $success = new \stdClass();
            $success->message = 'Synchronization done, no warning.';
            $success->status = 'success';
            $notificationsArray[] = $success;
        }

        return redirect()->action('WebsiteController@show', ['id' => $id])->with('notifications', $notificationsArray);
    }

    /**
     * Synchronize all websites with their remote contents
     *
     * @return void
     */
    public function syncAll() {
        // Ensure that this route isn't callable by a webbrowser
        if (php_sapi_name() != 'cli') {
            exit;
        }

        // Run Sync on all websites
        $exitCode = \Artisan::call('websites:enqueue');

        exit;
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
