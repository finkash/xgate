<?php

namespace Tests\Unit;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleReactionActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_reaction_when_none_exists(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $user->id,
            'content' => 'First post',
        ]);

        $action = new ToggleReactionAction();
        $counts = $action->execute($user, $post, ReactionType::LIKE);

        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => $post->id,
            'type' => ReactionType::LIKE->value,
        ]);

        $this->assertSame(1, $counts['like']);
        $this->assertSame(0, $counts['love']);
    }

    public function test_it_deletes_reaction_when_same_type_is_sent_again(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $user->id,
            'content' => 'Second post',
        ]);

        $action = new ToggleReactionAction();
        $action->execute($user, $post, ReactionType::LOVE);
        $counts = $action->execute($user, $post, ReactionType::LOVE);

        $this->assertDatabaseMissing('reactions', [
            'user_id' => $user->id,
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => $post->id,
        ]);

        $this->assertSame(0, $counts['love']);
    }

    public function test_it_updates_reaction_when_type_changes(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $user->id,
            'content' => 'Third post',
        ]);

        $action = new ToggleReactionAction();
        $action->execute($user, $post, ReactionType::LIKE);
        $counts = $action->execute($user, $post, ReactionType::WOW);

        $reaction = Reaction::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($reaction);
        $this->assertSame(ReactionType::WOW, $reaction->type);
        $this->assertSame(0, $counts['like']);
        $this->assertSame(1, $counts['wow']);
    }

    public function test_it_works_for_comment_reactions_using_polymorphism(): void
    {
        $author = User::factory()->create();
        $reactor = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'Commented post',
        ]);

        $comment = Comment::query()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'content' => 'Top level comment',
        ]);

        $action = new ToggleReactionAction();
        $counts = $action->execute($reactor, $comment, ReactionType::LAUGH);

        $this->assertDatabaseHas('reactions', [
            'user_id' => $reactor->id,
            'reactable_type' => $comment->getMorphClass(),
            'reactable_id' => $comment->id,
            'type' => ReactionType::LAUGH->value,
        ]);

        $this->assertSame(1, $counts['laugh']);
    }
}
