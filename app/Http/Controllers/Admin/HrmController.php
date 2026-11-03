<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class HrmController extends Controller
{
    /** GET /admin/hrm — placeholder shell for the upcoming HRM module */
    public function index()
    {
        return view('admin.hrm.index');
    }
}
