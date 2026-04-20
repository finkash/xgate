<?php

namespace App\Http\Controllers;

use App\Domain\Content\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __construct(
        private readonly FeedService $feedService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $feed = $this->feedService->getFeed($request->user(), $perPage);

        return response()->json($feed);
    }
}
