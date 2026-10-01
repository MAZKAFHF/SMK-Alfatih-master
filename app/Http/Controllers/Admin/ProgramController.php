<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Models\Program;
use App\Services\AuditService;
use App\Services\HtmlSanitizer;
use App\Services\MediaService;
use App\Services\PublicCacheService;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $programs = Program::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->string('search')}%")->orWhere('slug', 'like', "%{$request->string('search')}%"))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('order')->latest()
            ->paginate(15)->withQueryString();

        $trashedCount = Program::onlyTrashed()->count();

        return view('admin.programs.index', compact('programs', 'trashedCount'));
    }

    public function create()
    {
        return view('admin.programs.create');
    }

    public function store(StoreProgramRequest $request)
    {
        $data = $request->validated();
        $data['description'] = HtmlSanitizer::clean($data['description']);

        if ($request->hasFile('image')) {
            $data['image'] = MediaService::store($request->file('image'), 'programs');
        }

        $program = Program::create($data);
        $this->clearCache();
        AuditService::log('program_create', $program, null, $data);

        return redirect()->route('admin.programs.index')->with('success', 'Program berhasil ditambahkan.');
    }

    public function edit(Program $program)
    {
        return view('admin.programs.edit', compact('program'));
    }

    public function update(UpdateProgramRequest $request, Program $program)
    {
        $old = $program->toArray();
        $data = $request->validated();
        $data['description'] = HtmlSanitizer::clean($data['description']);

        if ($request->hasFile('image')) {
            $data['image'] = MediaService::replace(
                $request->file('image'),
                'programs',
                $program->getRawOriginal('image'),
            );
        } else {
            unset($data['image']);
        }

        $program->update($data);
        $this->clearCache();
        AuditService::log('program_update', $program, $old, $program->toArray());

        return redirect()->route('admin.programs.index')->with('success', 'Program berhasil diperbarui.');
    }

    public function destroy(Program $program)
    {
        $program->delete();
        AuditService::log('program_delete', $program);
        $this->clearCache();

        return back()->with('success', 'Program dipindahkan ke Trash.');
    }

    public function trash(Request $request)
    {
        $programs = Program::onlyTrashed()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->string('search')}%"))
            ->orderBy('order')->paginate(15)->withQueryString();

        return view('admin.programs.trash', compact('programs'));
    }

    public function restore(int $id)
    {
        $program = Program::onlyTrashed()->findOrFail($id);
        $program->restore();
        AuditService::log('program_restore', $program);
        $this->clearCache();

        return back()->with('success', 'Program dipulihkan.');
    }

    public function forceDelete(int $id)
    {
        $program = Program::onlyTrashed()->findOrFail($id);
        if ($program->getRawOriginal('image')) {
            MediaService::delete($program->getRawOriginal('image'));
        }
        $label = $program->name;
        $program->forceDelete();
        AuditService::log('program_force_delete', null, null, ['name' => $label]);
        $this->clearCache();

        return back()->with('success', 'Program dihapus permanen.');
    }

    private function clearCache(): void
    {
        PublicCacheService::forgetPrograms();
        PublicCacheService::forgetPages();
    }
}
