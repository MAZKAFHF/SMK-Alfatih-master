<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\ApplicationDocument;
use App\Models\DocumentRevision;
use App\Models\PPDBRegistration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public static function deleteAllFor(PPDBRegistration $app): void
    {
        $app->loadMissing('documents.revisions');
        foreach ($app->documents as $document) {
            collect([$document->path])->merge($document->revisions->pluck('path'))->filter()->unique()
                ->each(fn ($path) => Storage::disk($document->disk ?: 'ppdb_private')->delete($path));
        }
    }

    public static function ensurePlaceholders(PPDBRegistration $app): void
    {
        foreach (DocumentType::cases() as $type) {
            ApplicationDocument::firstOrCreate(
                ['application_id' => $app->id, 'type' => $type->value],
                ['status' => DocumentStatus::NotUploaded->value, 'disk' => 'ppdb_private', 'version' => 1]
            );
        }
    }

    public static function store(PPDBRegistration $app, DocumentType $type, UploadedFile $file, ?int $uploaderId = null): ApplicationDocument
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
            $ext = 'bin';
        }
        $name = Str::random(32).'.'.$ext;
        $path = "period-".($app->period_id ?? 'legacy')."/app-{$app->id}/{$type->value}_v".time()."_{$name}";
        Storage::disk('ppdb_private')->putFileAs(dirname($path), $file, basename($path));

        $doc = ApplicationDocument::firstOrNew(['application_id' => $app->id, 'type' => $type->value]);
        $isReplace = $doc->exists && $doc->path;
        $newVersion = $isReplace ? ((int) $doc->version + 1) : 1;

        if ($isReplace) {
            DocumentRevision::create([
                'document_id' => $doc->id, 'path' => $doc->path,
                'original_name' => $doc->original_name, 'version' => (int) $doc->version,
                'uploaded_by' => $uploaderId,
            ]);
        }

        $doc->fill([
            'disk' => 'ppdb_private', 'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(), 'size' => $file->getSize(),
            'version' => $newVersion,
            'status' => $isReplace ? DocumentStatus::Replaced->value : DocumentStatus::Uploaded->value,
        ])->save();

        return $doc->fresh();
    }
}
