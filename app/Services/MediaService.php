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
                $manager = new ImageManager(new Driver);
                $image = $manager->read($file->getRealPath());
                if ($image->width() > $maxWidth) {
                    $image->scale(width: $maxWidth);
                }
                $encoded = match ($ext) {
                    'png' => $image->toPng(),
                    'webp' => $image->toWebp(quality: $quality),
                    default => $image->toJpeg(quality: $quality),
                };
                if (Storage::disk('public')->put($relative, (string) $encoded)) {
                    return self::verifiedPath($relative);
                }
            } catch (\Throwable) {
                // fallback to raw store
            }
        }

        $storedPath = $file->storeAs(trim($directory, '/'), $filename, 'public');
        if ($storedPath === false) {
            throw new \RuntimeException('Gambar gagal disimpan. Silakan coba lagi.');
        }

        return self::verifiedPath($relative);
    }

    /**
     * Store a replacement before removing the old file. This prevents a failed
     * upload from leaving existing public content without an image.
     */
    public static function replace(
        UploadedFile $file,
        string $directory,
        ?string $oldPath,
        int $maxWidth = 1600,
        int $quality = 80,
    ): string {
        $newPath = self::store($file, $directory, $maxWidth, $quality);

        if ($oldPath && $oldPath !== $newPath) {
            self::delete($oldPath);
        }

        return $newPath;
    }

    public static function delete(?string $path): void
    {
        if ($path && (str_contains($path, '..') || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path))) {
            throw new \InvalidArgumentException('Path media tidak aman untuk dihapus.');
        }
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        // The path may already be an absolute/external URL.
        if (str_starts_with($path, 'http') || str_starts_with($path, '/storage')) {
            return $path;
        }

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }

    public static function isImage(UploadedFile $file): bool
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        return in_array($file->getMimeType(), $allowed, true);
    }

    private static function verifiedPath(string $relative): string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($relative)) {
            throw new \RuntimeException('Gambar gagal disimpan. Silakan coba lagi.');
        }

        return $relative;
    }
}
