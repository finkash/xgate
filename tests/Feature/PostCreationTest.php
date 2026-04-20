<?php

namespace Tests\Feature;

use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostMedia;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_text_only_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('posts.store'), [
            'content' => 'Hello social world',
        ]);

        $response->assertCreated()
            ->assertJson([
                'content' => 'Hello social world',
                'media_count' => 0,
            ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'content' => 'Hello social world',
        ]);
    }

    public function test_authenticated_user_can_create_post_with_image_and_video(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $image = UploadedFile::fake()->create('cover.jpg', 512, 'image/jpeg');
        $video = UploadedFile::fake()->create('clip.mp4', 5000, 'video/mp4');

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'content' => 'Post with media',
            'media' => [$image, $video],
        ]);

        $response->assertCreated();

        $post = Post::query()->where('user_id', $user->id)->latest('created_at')->first();

        $this->assertNotNull($post);
        $this->assertSame(2, $post->media()->count());

        $mediaRows = PostMedia::query()->where('post_id', $post->id)->orderBy('display_order')->get();
        $this->assertSame('image', $mediaRows[0]->type->value);
        $this->assertSame('video', $mediaRows[1]->type->value);

        foreach ($mediaRows as $media) {
            Storage::disk('public')->assertExists($media->file_path);
        }
    }

    public function test_it_rejects_invalid_media_type(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 120, 'application/pdf');

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'content' => 'Trying bad file',
            'media' => [$file],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('media');

        $this->assertDatabaseCount('post_media', 0);
    }

    public function test_it_rejects_image_larger_than_five_mb(): void
    {
        $user = User::factory()->create();
        $image = UploadedFile::fake()->create('big.jpg', 6000, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'content' => 'Trying big image',
            'media' => [$image],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('media');

        $this->assertDatabaseCount('post_media', 0);
    }

    public function test_user_can_update_own_post_text(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $user->id,
            'content' => 'Original post text',
        ]);

        $response = $this->actingAs($user)
            ->patchJson(route('posts.update', $post), [
                'content' => 'Updated post text',
            ]);

        $response->assertOk()->assertJson([
            'id' => $post->id,
            'content' => 'Updated post text',
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'content' => 'Updated post text',
        ]);
    }

    public function test_user_cannot_update_someone_elses_post(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $owner->id,
            'content' => 'Owner content',
        ]);

        $this->actingAs($intruder)
            ->patchJson(route('posts.update', $post), [
                'content' => 'Hacked content',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'content' => 'Owner content',
        ]);
    }

    public function test_user_can_delete_post_and_related_entities(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $other = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $owner->id,
            'content' => 'Delete me',
        ]);

        $path = UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4')->store('posts/'.$owner->id, 'public');
        $media = PostMedia::query()->create([
            'post_id' => $post->id,
            'file_path' => $path,
            'type' => 'video',
            'display_order' => 0,
            'alt_text' => null,
        ]);

        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $other->id,
            'content' => 'Comment to remove',
        ]);

        $post->reactions()->create([
            'user_id' => $other->id,
            'type' => 'like',
        ]);

        $comment->reactions()->create([
            'user_id' => $owner->id,
            'type' => 'love',
        ]);

        $this->actingAs($owner)
            ->deleteJson(route('posts.destroy', $post))
            ->assertNoContent();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('post_media', ['id' => $media->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);

        $this->assertDatabaseMissing('reactions', [
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => (string) $post->id,
        ]);

        $this->assertDatabaseMissing('reactions', [
            'reactable_type' => $comment->getMorphClass(),
            'reactable_id' => (string) $comment->id,
        ]);
    }
}
