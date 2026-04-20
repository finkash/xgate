<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Post;
use App\Domain\Engagement\Actions\CreateCommentAction;
use App\Domain\Engagement\Actions\DeleteCommentAction;
use App\Domain\Engagement\Actions\UpdateCommentAction;
use App\Domain\Engagement\DTOs\CreateCommentDTO;
use App\Domain\Engagement\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private readonly CreateCommentAction $createCommentAction,
        private readonly UpdateCommentAction $updateCommentAction,
        private readonly DeleteCommentAction $deleteCommentAction
    ) {
    }

    public function index(Post $post): JsonResponse
    {
        $comments = Comment::query()
            ->where('post_id', $post->id)
            ->whereNull('parent_comment_id')
            ->with(['user.profile', 'replies' => function ($query): void {
                $query->orderBy('created_at')->with('user.profile');
            }])
            ->orderBy('created_at')
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'parent_comment_id' => ['nullable', 'string', 'exists:comments,id'],
        ]);

        $dto = CreateCommentDTO::fromValidated($validated);
        $comment = $this->createCommentAction->execute($request->user(), $post, $dto);

        if ($request->boolean('_redirect')) {
            return back()->with('status', 'Comment added.');
        }

        return response()->json($comment->load('user.profile'), 201);
    }

    public function update(Request $request, Post $post, Comment $comment): JsonResponse|RedirectResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        if ((int) $comment->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $updated = $this->updateCommentAction->execute($comment, $validated['content']);

        if ($request->boolean('_redirect')) {
            return back()->with('status', 'Comment updated.');
        }

        return response()->json($updated);
    }

    public function destroy(Request $request, Post $post, Comment $comment): JsonResponse|RedirectResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        if ((int) $comment->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $this->deleteCommentAction->execute($comment);

        if ($request->boolean('_redirect')) {
            return back()->with('status', 'Comment deleted.');
        }

        return response()->json([], 204);
    }
}
