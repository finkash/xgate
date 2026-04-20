<?php

namespace App\Domain\Engagement\DTOs;

class CreateCommentDTO
{
    public function __construct(
        public readonly string $content,
        public readonly ?string $parentCommentId
    ) {
    }

    /**
     * @param array{content:string,parent_comment_id?:string|null} $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            content: $validated['content'],
            parentCommentId: $validated['parent_comment_id'] ?? null
        );
    }
}
