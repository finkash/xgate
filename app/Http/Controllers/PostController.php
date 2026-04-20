<?php

namespace App\Http\Controllers;

use App\Domain\Content\Actions\CreatePostAction;
use App\Domain\Content\DTOs\CreatePostDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private readonly CreatePostAction $createPostAction
    ) {
    }

    public function store(Request $request): JsonResponse
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

        return response()->json([
            'id' => $post->id,
            'content' => $post->content,
            'media_count' => $post->media->count(),
        ], 201);
    }
}
