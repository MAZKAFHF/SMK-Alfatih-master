<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TrashController extends Controller
{
    public function index()
    {
        return view('admin.trash.index');
    }
}
