<?php

namespace Tests\Unit;

use App\Domain\Content\Models\Post;
use App\Domain\Content\Services\FeedService;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use App\Domain\IdentityAndAccess\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_followed_posts_with_reaction_summary_and_current_user_reaction(): void
    {
        $viewer = User::factory()->create();
        $followed = User::factory()->create();
        $notFollowed = User::factory()->create();

        Follow::query()->create([
            'follower_id' => $viewer->id,
            'following_id' => $followed->id,
        ]);

        $followedPost = Post::query()->create([
            'user_id' => $followed->id,
            'content' => 'Visible post',
        ]);

        Post::query()->create([
            'user_id' => $notFollowed->id,
            'content' => 'Hidden post',
        ]);

        $comment = Comment::query()->create([
            'post_id' => $followedPost->id,
            'user_id' => $followed->id,
            'content' => 'Comment',
        ]);

        $followedPost->reactions()->create([
            'user_id' => $viewer->id,
            'type' => 'love',
        ]);

        $comment->reactions()->create([
            'user_id' => $viewer->id,
            'type' => 'wow',
        ]);

        $service = new FeedService();
        $feed = $service->getFeed($viewer, 15);

        $posts = $feed->getCollection();
        $this->assertCount(1, $posts);

        $post = $posts->first();
        $this->assertSame($followedPost->id, $post->id);
        $this->assertSame('love', $post->current_user_reaction);
        $this->assertSame(1, $post->reaction_summary['love']);

        $topComment = $post->topLevelComments->first();
        $this->assertNotNull($topComment);
        $this->assertSame('wow', $topComment->current_user_reaction);
        $this->assertSame(1, $topComment->reaction_summary['wow']);
    }

    public function test_discover_fallback_is_popularity_ordered_when_user_follows_nobody(): void
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create();

        $popularPost = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'Popular',
        ]);

        $recentButLessPopular = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'Less popular',
        ]);

        $popularPost->reactions()->create([
            'user_id' => $viewer->id,
            'type' => 'like',
        ]);

        $popularPost->reactions()->create([
            'user_id' => $author->id,
            'type' => 'love',
        ]);

        $recentButLessPopular->reactions()->create([
            'user_id' => $author->id,
            'type' => 'laugh',
        ]);

        $service = new FeedService();
        $feed = $service->getFeed($viewer, 15);

        $posts = $feed->getCollection()->values();
        $this->assertCount(2, $posts);
        $this->assertSame($popularPost->id, $posts[0]->id);
        $this->assertSame('like', $posts[0]->current_user_reaction);
        $this->assertSame(2, array_sum($posts[0]->reaction_summary));
    }
}
