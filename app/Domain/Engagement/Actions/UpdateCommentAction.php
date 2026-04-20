<?php

namespace App\Domain\Engagement\Actions;

use App\Domain\Engagement\Models\Comment;

class UpdateCommentAction
{
    public function execute(Comment $comment, string $content): Comment
    {
        $comment->update([
            'content' => trim($content),
        ]);

        return $comment->fresh() ?? $comment;
    }
}
