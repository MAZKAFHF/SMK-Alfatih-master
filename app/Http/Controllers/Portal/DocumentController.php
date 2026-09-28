<?php

namespace App\Http\Controllers\Portal;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\PPDBRegistration;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function upload(Request $request, PPDBRegistration $application, string $type)
    {
        $this->authorize('uploadDocument', $application);
        $docType = DocumentType::tryFrom($type);
        abort_unless($docType, 404);

        $existing = $application->documents()->where('type', $docType->value)->first();
        if ($application->application_status->value === 'needs_revision' && $existing?->status?->value !== 'needs_revision') {
            abort(403, 'Hanya dokumen yang diminta perbaikan yang dapat diganti.');
        }

        $request->validate([
            'file' => ['required', 'file', $docType === DocumentType::Foto ? 'mimes:jpg,jpeg,png,webp' : 'mimes:jpg,jpeg,png,webp,pdf', 'max:'.$docType->maxKb()],
        ], [], ['file' => $docType->label()]);

        $doc = DocumentService::store($application, $docType, $request->file('file'), $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $docType->label().' berhasil diunggah.',
                'document' => ['type' => $doc->type->value, 'name' => $doc->original_name, 'status' => $doc->status->label()],
                'next_action' => $application->fresh()->nextPortalAction(),
            ]);
        }

        return back()->with('success', $docType->label().' berhasil diunggah.');
    }

    /** Preview privat: hanya pemilik atau admin. */
    public function preview(Request $request, ApplicationDocument $document)
    {
        $app = $document->application;
        if (! $request->user()->is_admin && (int) $app->applicant_account_id !== (int) $request->user()->id) {
            abort(403);
        }
        abort_unless($document->path && Storage::disk($document->disk ?: 'ppdb_private')->exists($document->path), 404);

        $mime = $document->mime ?: 'application/octet-stream';

        return new StreamedResponse(function () use ($document) {
            echo Storage::disk($document->disk ?: 'ppdb_private')->get($document->path);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($document->original_name ?: $document->path).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
