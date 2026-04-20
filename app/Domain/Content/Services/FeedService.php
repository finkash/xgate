<?php

namespace App\Domain\Content\Services;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Engagement\Models\Reaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

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
                'topLevelComments.user.profile',
                'topLevelComments.replies.user.profile',
            ])
            ->withCount('comments');

        if ($followedUserIds->isNotEmpty()) {
            $query
                ->where(function ($builder) use ($followedUserIds, $user): void {
                    $builder->whereIn('user_id', $followedUserIds)
                        ->orWhere('user_id', $user->id);
                })
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

        $this->hydrateEngagementAttributes($posts, $user);

        return $paginator;
    }

    public function hydrateEngagementAttributes(Collection $posts, ?User $user = null): void
    {
        if ($posts->isEmpty()) {
            return;
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

            $typeKey = $this->reactionTypeValue($row->type);

            $byPost[$postId][$typeKey] = (int) $row->aggregate;
        }

        $currentByPost = [];
        if ($user) {
            $postReactions = Reaction::query()
                ->select(['reactable_id', 'type'])
                ->where('user_id', $user->id)
                ->where('reactable_type', (new Post())->getMorphClass())
                ->whereIn('reactable_id', $postIds)
                ->get();

            foreach ($postReactions as $row) {
                $currentByPost[$row->reactable_id] = $this->reactionTypeValue($row->type);
            }
        }

        $posts->transform(function (Post $post) use ($byPost, $currentByPost): Post {
            $post->setAttribute('reaction_summary', $byPost[$post->id] ?? $this->emptyReactionSummary());
            $post->setAttribute('current_user_reaction', $currentByPost[$post->id] ?? null);

            return $post;
        });

        $commentIds = $posts
            ->flatMap(function (Post $post) {
                return $post->topLevelComments->flatMap(function ($comment) {
                    return collect([$comment->id])->merge($comment->replies->pluck('id'));
                });
            })
            ->unique()
            ->values();

        if ($commentIds->isNotEmpty()) {
            $commentCounts = Reaction::query()
                ->selectRaw('reactable_id, type, COUNT(*) as aggregate')
                ->where('reactable_type', (new Comment())->getMorphClass())
                ->whereIn('reactable_id', $commentIds)
                ->groupBy('reactable_id', 'type')
                ->get();

            $byComment = [];

            foreach ($commentIds as $commentId) {
                $byComment[$commentId] = $this->emptyReactionSummary();
            }

            foreach ($commentCounts as $row) {
                $commentId = $row->reactable_id;
                if (! isset($byComment[$commentId])) {
                    $byComment[$commentId] = $this->emptyReactionSummary();
                }

                $typeKey = $this->reactionTypeValue($row->type);

                $byComment[$commentId][$typeKey] = (int) $row->aggregate;
            }

            $currentByComment = [];
            if ($user) {
                $commentReactions = Reaction::query()
                    ->select(['reactable_id', 'type'])
                    ->where('user_id', $user->id)
                    ->where('reactable_type', (new Comment())->getMorphClass())
                    ->whereIn('reactable_id', $commentIds)
                    ->get();

                foreach ($commentReactions as $row) {
                    $currentByComment[$row->reactable_id] = $this->reactionTypeValue($row->type);
                }
            }

            $posts->each(function (Post $post) use ($byComment, $currentByComment): void {
                $post->topLevelComments->each(function ($comment) use ($byComment, $currentByComment): void {
                    $comment->setAttribute('reaction_summary', $byComment[$comment->id] ?? $this->emptyReactionSummary());
                    $comment->setAttribute('current_user_reaction', $currentByComment[$comment->id] ?? null);

                    $comment->replies->each(function ($reply) use ($byComment, $currentByComment): void {
                        $reply->setAttribute('reaction_summary', $byComment[$reply->id] ?? $this->emptyReactionSummary());
                        $reply->setAttribute('current_user_reaction', $currentByComment[$reply->id] ?? null);
                    });
                });
            });
        }
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

    private function reactionTypeValue(mixed $type): string
    {
        return $type instanceof ReactionType
            ? $type->value
            : (string) $type;
    }
}
