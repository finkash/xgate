<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        $user->load('profile');

        $posts = Post::query()
            ->where('user_id', $user->id)
            ->with([
                'media',
                'topLevelComments.user.profile',
                'topLevelComments.replies.user.profile',
            ])
            ->withCount(['comments', 'reactions'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();

        $isFollowing = false;
        if (auth()->check() && auth()->id() !== $user->id) {
            $isFollowing = auth()->user()
                ->following()
                ->where('following_id', $user->id)
                ->exists();
        }

        $followers = $user->followers()
            ->select('users.id', 'users.username')
            ->with('profile')
            ->orderBy('users.username')
            ->get();

        $following = $user->following()
            ->select('users.id', 'users.username')
            ->with('profile')
            ->orderBy('users.username')
            ->get();

        return view('livewire.profile.show', [
            'profileUser' => $user,
            'posts' => $posts,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'isFollowing' => $isFollowing,
            'followers' => $followers,
            'following' => $following,
        ]);
    }

    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile');

        return view('livewire.profile.edit', [
            'profileUser' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        $payload = [
            'bio' => $validated['bio'] ?? null,
            'location' => $validated['location'] ?? null,
            'website' => $validated['website'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $payload['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('cover')) {
            $payload['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $profile->update($payload);

        return redirect()
            ->route('profiles.show', ['user' => $user->username])
            ->with('status', 'Profile updated successfully.');
    }
}
