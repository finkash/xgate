<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\Models\Post;
use Illuminate\Validation\ValidationException;

class UpdatePostAction
{
    public function execute(Post $post, string $content): Post
    {
        $trimmed = trim($content);

        if ($trimmed === '') {
            throw ValidationException::withMessages([
                'content' => 'Post content cannot be empty.',
            ]);
        }

        $post->update([
            'content' => $trimmed,
        ]);

        return $post->refresh();
    }
}
