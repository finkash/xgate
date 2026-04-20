<?php

namespace App\Http\Controllers;

use App\Domain\IdentityAndAccess\Actions\ToggleFollowAction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        private readonly ToggleFollowAction $toggleFollowAction
    ) {
    }

    public function store(Request $request, User $user): JsonResponse
    {
        $created = $this->toggleFollowAction->follow($request->user(), $user);

        return response()->json([
            'following' => true,
            'created' => $created,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $deleted = $this->toggleFollowAction->unfollow($request->user(), $user);

        return response()->json([
            'following' => false,
            'deleted' => $deleted,
        ]);
    }
}
