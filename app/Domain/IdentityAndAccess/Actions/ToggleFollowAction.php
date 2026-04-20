<?php

namespace App\Domain\IdentityAndAccess\Actions;

use App\Domain\IdentityAndAccess\Models\Follow;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ToggleFollowAction
{
    /**
     * Follow a target user.
     *
     * @return bool True when a new follow relation was created.
     */
    public function follow(User $follower, User $following): bool
    {
        $this->guardAgainstSelfFollow($follower, $following);

        $follow = Follow::query()->firstOrCreate([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);

        return $follow->wasRecentlyCreated;
    }

    /**
     * Unfollow a target user.
     *
     * @return bool True when an existing follow relation was removed.
     */
    public function unfollow(User $follower, User $following): bool
    {
        $this->guardAgainstSelfFollow($follower, $following);

        $deleted = Follow::query()
            ->where('follower_id', $follower->id)
            ->where('following_id', $following->id)
            ->delete();

        return $deleted > 0;
    }

    protected function guardAgainstSelfFollow(User $follower, User $following): void
    {
        if ($follower->is($following)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot follow yourself.',
            ]);
        }
    }
}
