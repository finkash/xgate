<?php

namespace App\Domain\Content\Services;

use App\Domain\Content\Enums\MediaType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class MediaUploadService
{
    /**
     * @var array<int, string>
     */
    private array $imageMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * @var array<int, string>
     */
    private array $videoMimes = [
        'video/mp4',
        'video/webm',
    ];

    /**
     * @param array<int, UploadedFile> $files
     * @return array<int, array{file_path: string, type: string, display_order: int, alt_text: null}>
     */
    public function storeMany(array $files, User $user): array
    {
        $stored = [];

        foreach ($files as $index => $file) {
            $mediaType = $this->resolveAndValidateType($file);
            $path = $file->store('posts/'.$user->id, 'public');

            $stored[] = [
                'file_path' => $path,
                'type' => $mediaType->value,
                'display_order' => $index,
                'alt_text' => null,
            ];
        }

        return $stored;
    }

    private function resolveAndValidateType(UploadedFile $file): MediaType
    {
        $mimeType = $file->getMimeType() ?? '';

        if (in_array($mimeType, $this->imageMimes, true)) {
            if ($file->getSize() > 5 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'media' => 'Images must not exceed 5 MB each.',
                ]);
            }

            return MediaType::IMAGE;
        }

        if (in_array($mimeType, $this->videoMimes, true)) {
            if ($file->getSize() > 50 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'media' => 'Videos must not exceed 50 MB each.',
                ]);
            }

            return MediaType::VIDEO;
        }

        throw ValidationException::withMessages([
            'media' => 'Only jpg, png, webp, mp4, and webm files are allowed.',
        ]);
    }
}
