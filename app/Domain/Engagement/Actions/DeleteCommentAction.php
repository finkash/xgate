<?php

namespace App\Domain\Engagement\Actions;

use App\Domain\Engagement\Models\Comment;

class DeleteCommentAction
{
    public function execute(Comment $comment): void
    {
        $comment->delete();
    }
}
