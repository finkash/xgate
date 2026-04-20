<?php

namespace App\Http\Controllers;

use App\Domain\IdentityAndAccess\Actions\ToggleFollowAction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        private readonly ToggleFollowAction $toggleFollowAction
    ) {
    }

    public function store(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $created = $this->toggleFollowAction->follow($request->user(), $user);

        if ($request->boolean('_redirect')) {
            return back()->with('status', $created ? 'Now following user.' : 'Already following user.');
        }

        return response()->json([
            'following' => true,
            'created' => $created,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $deleted = $this->toggleFollowAction->unfollow($request->user(), $user);

        if ($request->boolean('_redirect')) {
            return back()->with('status', $deleted ? 'Unfollowed user.' : 'User was not followed.');
        }

        return response()->json([
            'following' => false,
            'deleted' => $deleted,
        ]);
    }
}
