<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PpdbAvailability;

class PPDBController extends Controller
{
    public function index()
    {
        $ppdbState = PpdbAvailability::resolvePublic();
        $ppdb = $ppdbState->period;

        return view('public.ppdb.index', compact('ppdb', 'ppdbState'))
            ->with('title', 'PPDB Online');
    }
}
