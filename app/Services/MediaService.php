<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaService
{
    /**
     * Store uploaded image safely.
     *
     * @return string path stored relative to disk public (e.g., programs/abc.webp)
     */
    public static function store(UploadedFile $file, string $directory, int $maxWidth = 1600, int $quality = 80): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $ext = 'webp';
        }

        $filename = Str::random(28).'.'.$ext;
        $relative = trim($directory, '/').'/'.$filename;

        // Validate mime thoroughly
        $mime = $file->getMimeType();
        if (! str_starts_with((string) $mime, 'image/')) {
            throw new \InvalidArgumentException('File harus berupa gambar.');
        }

        if (class_exists(ImageManager::class)) {
            try {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getRealPath());
                if ($image->width() > $maxWidth) {
                    $image->scale(width: $maxWidth);
                }
                $encoded = match ($ext) {
                    'png' => $image->toPng(),
                    'webp' => $image->toWebp(quality: $quality),
                    default => $image->toJpeg(quality: $quality),
                };
                Storage::disk('public')->put($relative, (string) $encoded);

                return $relative;
            } catch (\Throwable) {
                // fallback to raw store
            }
        }

        $file->storeAs($directory, $filename, 'public');

        return $relative;
    }

    public static function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        // path might already be url due to accessor; handle
        if (str_starts_with($path, 'http') || str_starts_with($path, '/storage')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public static function isImage(UploadedFile $file): bool
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        return in_array($file->getMimeType(), $allowed, true);
    }
}
