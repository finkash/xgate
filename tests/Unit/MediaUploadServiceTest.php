<?php

namespace Tests\Unit;

use App\Domain\Content\Services\MediaUploadService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaUploadServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_media_with_type_and_display_order(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $image = UploadedFile::fake()->create('photo.jpg', 1024, 'image/jpeg');
        $video = UploadedFile::fake()->create('clip.mp4', 5000, 'video/mp4');

        $service = new MediaUploadService();
        $rows = $service->storeMany([$image, $video], $user);

        $this->assertCount(2, $rows);
        $this->assertSame('image', $rows[0]['type']);
        $this->assertSame(0, $rows[0]['display_order']);
        $this->assertSame('video', $rows[1]['type']);
        $this->assertSame(1, $rows[1]['display_order']);

        Storage::disk('public')->assertExists($rows[0]['file_path']);
        Storage::disk('public')->assertExists($rows[1]['file_path']);
    }

    public function test_it_rejects_unsupported_media_type(): void
    {
        $this->expectException(ValidationException::class);

        $user = User::factory()->create();
        $bad = UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf');

        $service = new MediaUploadService();
        $service->storeMany([$bad], $user);
    }

    public function test_it_rejects_image_larger_than_five_mb(): void
    {
        $this->expectException(ValidationException::class);

        $user = User::factory()->create();
        $bigImage = UploadedFile::fake()->create('huge.jpg', 6000, 'image/jpeg');

        $service = new MediaUploadService();
        $service->storeMany([$bigImage], $user);
    }
}
