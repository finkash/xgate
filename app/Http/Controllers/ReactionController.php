<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\ToggleReactionAction;
use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\Engagement\Models\Comment;
use App\Domain\Engagement\Models\Reaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    public function __construct(
        private readonly ToggleReactionAction $toggleReactionAction
    ) {
    }

    public function togglePost(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_column(ReactionType::cases(), 'value'))],
        ]);

        $type = ReactionType::from($validated['type']);
        $counts = $this->toggleReactionAction->execute($request->user(), $post, $type);
        $current = $this->currentUserReactionType($request->user()->id, $post->getMorphClass(), (string) $post->id);

        return response()->json([
            'reactable_type' => 'post',
            'reactable_id' => $post->id,
            'current_user_reaction' => $current,
            'counts' => $counts,
        ]);
    }

    public function toggleComment(Request $request, Post $post, Comment $comment): JsonResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_column(ReactionType::cases(), 'value'))],
        ]);

        $type = ReactionType::from($validated['type']);
        $counts = $this->toggleReactionAction->execute($request->user(), $comment, $type);
        $current = $this->currentUserReactionType($request->user()->id, $comment->getMorphClass(), (string) $comment->id);

        return response()->json([
            'reactable_type' => 'comment',
            'reactable_id' => $comment->id,
            'current_user_reaction' => $current,
            'counts' => $counts,
        ]);
    }

    private function currentUserReactionType(int $userId, string $reactableType, string $reactableId): ?string
    {
        $reaction = Reaction::query()
            ->where('user_id', $userId)
            ->where('reactable_type', $reactableType)
            ->where('reactable_id', $reactableId)
            ->first();

        if (! $reaction) {
            return null;
        }

        return $reaction->type->value;
    }
}
