<?php

namespace Tests\Feature;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_comment_on_post(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $user->id,
            'content' => 'base post',
        ]);

        $response = $this->actingAs($user)->postJson(route('posts.comments.store', $post), [
            'content' => 'First comment',
        ]);

        $response->assertCreated()
            ->assertJson([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'parent_comment_id' => null,
                'content' => 'First comment',
            ]);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'First comment',
        ]);
    }

    public function test_reply_to_reply_is_flattened_under_original_parent(): void
    {
        $author = User::factory()->create();
        $replier = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'post content',
        ]);

        $parent = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'content' => 'Parent comment',
        ]);

        $firstReplyResponse = $this->actingAs($replier)->postJson(route('posts.comments.store', $post), [
            'content' => 'First reply',
            'parent_comment_id' => $parent->id,
        ]);

        $firstReplyResponse->assertCreated();
        $firstReplyId = $firstReplyResponse->json('id');

        $secondReplyResponse = $this->actingAs($author)->postJson(route('posts.comments.store', $post), [
            'content' => 'Reply to reply',
            'parent_comment_id' => $firstReplyId,
        ]);

        $secondReplyResponse->assertCreated();
        $this->assertSame($parent->id, $secondReplyResponse->json('parent_comment_id'));
    }

    public function test_user_can_edit_their_own_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $user->id,
            'content' => 'base post',
        ]);

        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Old content',
        ]);

        $response = $this->actingAs($user)->patchJson(route('posts.comments.update', [$post, $comment]), [
            'content' => 'Updated content',
        ]);

        $response->assertOk()->assertJson([
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    public function test_user_cannot_edit_someone_elses_comment(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $owner->id,
            'content' => 'base post',
        ]);

        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $owner->id,
            'content' => 'Owner content',
        ]);

        $response = $this->actingAs($other)->patchJson(route('posts.comments.update', [$post, $comment]), [
            'content' => 'Bad update',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_their_own_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $user->id,
            'content' => 'base post',
        ]);

        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Disposable',
        ]);

        $response = $this->actingAs($user)->deleteJson(route('posts.comments.destroy', [$post, $comment]));

        $response->assertNoContent();
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }
}
