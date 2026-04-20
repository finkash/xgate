<?php

namespace App\Domain\Content\DTOs;

use Illuminate\Http\UploadedFile;

class CreatePostDTO
{
    /**
     * @param array<int, UploadedFile> $media
     */
    public function __construct(
        public readonly ?string $content,
        public readonly array $media
    ) {
    }

    /**
     * @param array<int, UploadedFile> $media
     */
    public static function fromRequestData(?string $content, array $media): self
    {
        return new self(
            content: $content,
            media: $media
        );
    }
}
