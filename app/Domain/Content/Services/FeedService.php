<?php

namespace App\Domain\Content\Services;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Engagement\Models\Reaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FeedService
{
    public function getFeed(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $followedUserIds = DB::table('follows')
            ->where('follower_id', $user->id)
            ->pluck('following_id');

        $query = Post::query()
            ->with([
                'author.profile',
                'media',
            ])
            ->withCount('comments');

        if ($followedUserIds->isNotEmpty()) {
            $query->whereIn('user_id', $followedUserIds)
                ->orderByDesc('created_at');
        } else {
            $query->withCount('reactions as reactions_total')
                ->orderByDesc('reactions_total')
                ->orderByDesc('created_at');
        }

        $paginator = $query->paginate($perPage);
        $posts = $paginator->getCollection();

        if ($posts->isEmpty()) {
            return $paginator;
        }

        $postIds = $posts->pluck('id');

        $counts = Reaction::query()
            ->selectRaw('reactable_id, type, COUNT(*) as aggregate')
            ->where('reactable_type', (new Post())->getMorphClass())
            ->whereIn('reactable_id', $postIds)
            ->groupBy('reactable_id', 'type')
            ->get();

        $byPost = [];

        foreach ($postIds as $postId) {
            $byPost[$postId] = $this->emptyReactionSummary();
        }

        foreach ($counts as $row) {
            $postId = $row->reactable_id;
            if (! isset($byPost[$postId])) {
                $byPost[$postId] = $this->emptyReactionSummary();
            }

            $typeKey = $row->type instanceof ReactionType
                ? $row->type->value
                : (string) $row->type;

            $byPost[$postId][$typeKey] = (int) $row->aggregate;
        }

        $posts->transform(function (Post $post) use ($byPost): Post {
            $post->setAttribute('reaction_summary', $byPost[$post->id] ?? $this->emptyReactionSummary());

            return $post;
        });

        return $paginator;
    }

    /**
     * @return array<string, int>
     */
    private function emptyReactionSummary(): array
    {
        $summary = [];

        foreach (ReactionType::cases() as $type) {
            $summary[$type->value] = 0;
        }

        return $summary;
    }
}
