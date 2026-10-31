<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /** GET /dashboard — generic landing that links out to each module */
    public function index()
    {
        return view('admin.home');
    }
}
