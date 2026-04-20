<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\DTOs\CreatePostDTO;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Services\MediaUploadService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePostAction
{
    public function __construct(
        private readonly MediaUploadService $mediaUploadService
    ) {
    }

    public function execute(User $author, CreatePostDTO $dto): Post
    {
        $trimmed = trim((string) $dto->content);

        if ($trimmed === '' && count($dto->media) === 0) {
            throw ValidationException::withMessages([
                'content' => 'A post needs text content or at least one media file.',
            ]);
        }

        return DB::transaction(function () use ($author, $trimmed, $dto): Post {
            $post = Post::query()->create([
                'user_id' => $author->id,
                'content' => $trimmed,
            ]);

            if (count($dto->media) > 0) {
                $mediaRows = $this->mediaUploadService->storeMany($dto->media, $author);
                $post->media()->createMany($mediaRows);
            }

            return $post->fresh(['media']) ?? $post;
        });
    }
}
