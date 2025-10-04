<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ComponentDocsController extends Controller
{
    public function index()
    {
        return view('admin.component-docs');
    }
}
