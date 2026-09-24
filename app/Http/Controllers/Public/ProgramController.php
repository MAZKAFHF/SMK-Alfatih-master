<?php

namespace App\Http\Controllers\Public;

use App\Enums\ProgramStatus;
use App\Http\Controllers\Controller;
use App\Models\Program;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::active()->orderBy('order')->get();

        return view('public.programs.index', compact('programs'))
            ->with('title', 'Program Keahlian');
    }

    public function show(Program $program)
    {
        abort_unless($program->status === ProgramStatus::Active, 404);

        $otherPrograms = Program::active()
            ->whereKeyNot($program->id)
            ->orderBy('order')
            ->limit(3)
            ->get();

        return view('public.programs.show', compact('program', 'otherPrograms'))
            ->with('title', $program->name)
            ->with('description', $program->short_description);
    }
}
