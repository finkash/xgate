<?php

namespace Tests\Feature;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactionEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_react_to_post_and_get_counts_and_current_reaction(): void
    {
        $author = User::factory()->create();
        $reactor = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'post to react on',
        ]);

        $response = $this->actingAs($reactor)->postJson(route('posts.reactions.toggle', $post), [
            'type' => 'like',
        ]);

        $response->assertOk()->assertJson([
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'current_user_reaction' => 'like',
        ]);

        $this->assertSame(1, $response->json('counts.like'));
        $this->assertSame(0, $response->json('counts.love'));
    }

    public function test_same_reaction_type_toggles_off_for_post(): void
    {
        $author = User::factory()->create();
        $reactor = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'toggle post',
        ]);

        $this->actingAs($reactor)->postJson(route('posts.reactions.toggle', $post), [
            'type' => 'wow',
        ])->assertOk();

        $response = $this->actingAs($reactor)->postJson(route('posts.reactions.toggle', $post), [
            'type' => 'wow',
        ]);

        $response->assertOk()->assertJson([
            'current_user_reaction' => null,
        ]);

        $this->assertSame(0, $response->json('counts.wow'));
    }

    public function test_different_reaction_type_updates_existing_for_post(): void
    {
        $author = User::factory()->create();
        $reactor = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'update post',
        ]);

        $this->actingAs($reactor)->postJson(route('posts.reactions.toggle', $post), [
            'type' => 'like',
        ])->assertOk();

        $response = $this->actingAs($reactor)->postJson(route('posts.reactions.toggle', $post), [
            'type' => 'love',
        ]);

        $response->assertOk()->assertJson([
            'current_user_reaction' => 'love',
        ]);

        $this->assertSame(0, $response->json('counts.like'));
        $this->assertSame(1, $response->json('counts.love'));
    }

    public function test_user_can_react_to_comment_and_get_grouped_counts(): void
    {
        $author = User::factory()->create();
        $reactor = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'post with comment',
        ]);

        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'content' => 'comment body',
        ]);

        $response = $this->actingAs($reactor)->postJson(route('comments.reactions.toggle', [$post, $comment]), [
            'type' => 'laugh',
        ]);

        $response->assertOk()->assertJson([
            'reactable_type' => 'comment',
            'reactable_id' => $comment->id,
            'current_user_reaction' => 'laugh',
        ]);

        $this->assertSame(1, $response->json('counts.laugh'));
    }

    public function test_invalid_reaction_type_is_rejected(): void
    {
        $author = User::factory()->create();
        $reactor = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'invalid type post',
        ]);

        $response = $this->actingAs($reactor)->postJson(route('posts.reactions.toggle', $post), [
            'type' => 'fire',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('type');
    }
}
