<?php

namespace App\Domain\Engagement\Actions;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\DTOs\CreateCommentDTO;
use App\Domain\Engagement\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCommentAction
{
    public function execute(User $author, Post $post, CreateCommentDTO $dto): Comment
    {
        return DB::transaction(function () use ($author, $post, $dto): Comment {
            $resolvedParentId = null;

            if ($dto->parentCommentId !== null) {
                $parent = Comment::query()
                    ->where('id', $dto->parentCommentId)
                    ->where('post_id', $post->id)
                    ->first();

                if (! $parent) {
                    throw ValidationException::withMessages([
                        'parent_comment_id' => 'Parent comment was not found for this post.',
                    ]);
                }

                $resolvedParentId = $parent->parent_comment_id ?: $parent->id;
            }

            return Comment::query()->create([
                'post_id' => $post->id,
                'user_id' => $author->id,
                'parent_comment_id' => $resolvedParentId,
                'content' => trim($dto->content),
            ]);
        });
    }
}
