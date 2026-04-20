<?php

namespace Tests\Feature;

use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\PostMedia;
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
}
