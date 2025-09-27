<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminDocumentationController extends Controller
{
    public function index()
    {
        return view('admin.documentation');
    }
}
