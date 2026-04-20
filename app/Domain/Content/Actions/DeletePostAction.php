<?php

namespace App\Domain\Content\Actions;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use Illuminate\Support\Facades\DB;

class DeletePostAction
{
    public function execute(Post $post): void
    {
        DB::transaction(function () use ($post): void {
            $commentIds = $post->comments()->pluck('id');

            if ($commentIds->isNotEmpty()) {
                Reaction::query()
                    ->where('reactable_type', (new Comment())->getMorphClass())
                    ->whereIn('reactable_id', $commentIds)
                    ->delete();
            }

            $post->reactions()->delete();
            $post->comments()->forceDelete();
            $post->media()->delete();
            $post->forceDelete();
        });
    }
}
