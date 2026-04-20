<?php

namespace App\Http\Controllers;

use App\Domain\Content\Actions\CreatePostAction;
use App\Domain\Content\Actions\DeletePostAction;
use App\Domain\Content\Actions\UpdatePostAction;
use App\Domain\Content\DTOs\CreatePostDTO;
use App\Domain\Content\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private readonly CreatePostAction $createPostAction,
        private readonly UpdatePostAction $updatePostAction,
        private readonly DeletePostAction $deletePostAction
    ) {
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'max:51200'],
        ]);

        $dto = CreatePostDTO::fromRequestData(
            $validated['content'] ?? null,
            $request->file('media', [])
        );

        $post = $this->createPostAction->execute($request->user(), $dto);

        if ($request->boolean('_redirect')) {
            return redirect()->route('dashboard')->with('status', 'Post created successfully.');
        }

        return response()->json([
            'id' => $post->id,
            'content' => $post->content,
            'media_count' => $post->media->count(),
        ], 201);
    }

    public function update(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        if ((int) $post->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $updated = $this->updatePostAction->execute($post, $validated['content']);

        if ($request->boolean('_redirect')) {
            return back()->with('status', 'Post updated successfully.');
        }

        return response()->json([
            'id' => $updated->id,
            'content' => $updated->content,
        ]);
    }

    public function destroy(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        if ((int) $post->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $this->deletePostAction->execute($post);

        if ($request->boolean('_redirect')) {
            return back()->with('status', 'Post deleted successfully.');
        }

        return response()->json([], 204);
    }
}
