<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SeoAudit;

class SeoController extends Controller
{
    public function index()
    {
        return view('admin.seo.index', [
            'report'  => SeoAudit::latest(),
            'history' => SeoAudit::history(14),
        ]);
    }

    public function run()
    {
        @set_time_limit(180);
        SeoAudit::run();

        return redirect()->route('admin.seo.index', ['done' => 1]);
    }
}
