<?php

namespace Tests\Unit;

use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    public function test_failed_replacement_keeps_existing_media(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('programs/existing.jpg', 'existing image');

        try {
            MediaService::replace(
                UploadedFile::fake()->createWithContent('not-an-image.txt', 'not an image'),
                'programs',
                'programs/existing.jpg',
            );

            $this->fail('Invalid media replacement should fail.');
        } catch (\InvalidArgumentException) {
            Storage::disk('public')->assertExists('programs/existing.jpg');
        }
    }
}
