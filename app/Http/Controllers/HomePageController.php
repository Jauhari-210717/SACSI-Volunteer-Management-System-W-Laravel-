<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function index()
    {
        // Empty arrays for testing "no events"
        $ongoingEvents = [];
        $upcomingEvents = [];

        return view('homepage.homepage', compact('ongoingEvents', 'upcomingEvents'));
    }
}
