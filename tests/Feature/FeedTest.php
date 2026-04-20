<?php

namespace Tests\Feature;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\IdentityAndAccess\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_shows_only_followed_users_posts_ordered_newest_first(): void
    {
        $viewer = User::factory()->create();
        $followedA = User::factory()->create();
        $followedB = User::factory()->create();
        $notFollowed = User::factory()->create();

        Follow::query()->create([
            'follower_id' => $viewer->id,
            'following_id' => $followedA->id,
        ]);

        Follow::query()->create([
            'follower_id' => $viewer->id,
            'following_id' => $followedB->id,
        ]);

        $olderFollowed = Post::query()->create([
            'user_id' => $followedA->id,
            'content' => 'followed older',
        ]);
        $olderFollowed->forceFill([
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30),
        ])->save();

        $newestFollowed = Post::query()->create([
            'user_id' => $followedB->id,
            'content' => 'followed newest',
        ]);
        $newestFollowed->forceFill([
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ])->save();

        Post::query()->create([
            'user_id' => $notFollowed->id,
            'content' => 'should not appear',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($viewer)->getJson(route('feed.index'));

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertSame($newestFollowed->id, $data[0]['id']);
        $this->assertSame($olderFollowed->id, $data[1]['id']);
    }

    public function test_feed_falls_back_to_discover_and_sorts_by_reaction_count(): void
    {
        $viewer = User::factory()->create();
        $authorA = User::factory()->create();
        $authorB = User::factory()->create();

        $postPopular = Post::query()->create([
            'user_id' => $authorA->id,
            'content' => 'popular post',
        ]);

        $postLessPopular = Post::query()->create([
            'user_id' => $authorB->id,
            'content' => 'less popular post',
        ]);

        $toggle = new ToggleReactionAction();

        $reactor1 = User::factory()->create();
        $reactor2 = User::factory()->create();
        $reactor3 = User::factory()->create();

        $toggle->execute($reactor1, $postPopular, ReactionType::LIKE);
        $toggle->execute($reactor2, $postPopular, ReactionType::LOVE);
        $toggle->execute($reactor3, $postPopular, ReactionType::WOW);

        $toggle->execute($reactor1, $postLessPopular, ReactionType::LIKE);

        $response = $this->actingAs($viewer)->getJson(route('feed.index'));

        $response->assertOk();

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertSame($postPopular->id, $data[0]['id']);
        $this->assertSame(3, array_sum($data[0]['reaction_summary']));
    }
}
