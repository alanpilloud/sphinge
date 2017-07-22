<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Website;

class WebsiteScoresController extends Controller
{
    /**
     * Display a listing of scores for a given website.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id) 
    {
        $website = Website::findOrFail($id);
        return view('website.scores', ['website' => $website, 'scores' => $website->scores]);
    }
}

