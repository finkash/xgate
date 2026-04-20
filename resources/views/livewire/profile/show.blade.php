@extends('layouts.neon')

@section('title', '@'.$profileUser->username.' | '.config('app.name', 'xgate'))

@section('content')
    @php
        $cover = $profileUser->profile->cover_path ?? null;
        if ($cover && str_starts_with($cover, 'http')) {
            $coverSrc = $cover;
        } elseif ($cover) {
            $coverSrc = route('media.show', ['path' => ltrim($cover, '/')]);
        } else {
            $coverSrc = null;
        }

        $avatar = $profileUser->profile->avatar_path ?? null;
        if ($avatar && str_starts_with($avatar, 'http')) {
            $avatarSrc = $avatar;
        } elseif ($avatar) {
            $avatarSrc = route('media.show', ['path' => ltrim($avatar, '/')]);
        } else {
            $avatarSrc = null;
        }
    @endphp

    <div x-data="{ openList: null }">
        <section class="overflow-hidden rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 shadow-[0_0_20px_rgba(34,211,238,0.15)]">
            <div class="h-44 w-full bg-[#091226]">
                @if($coverSrc)
                    <img src="{{ $coverSrc }}" alt="cover" class="h-full w-full object-cover object-center" />
                @endif
            </div>

            <div class="relative z-20 p-5">
                <div class="-mt-14 flex flex-wrap items-end justify-between gap-4">
                    <div class="flex items-end gap-4">
                        @if($avatarSrc)
                            <img src="{{ $avatarSrc }}" alt="avatar" class="relative z-20 h-24 w-24 rounded-full border-4 border-[#0e1530] object-cover shadow-[0_0_18px_rgba(34,211,238,0.25)]" />
                        @else
                            <div class="relative z-20 flex h-24 w-24 items-center justify-center rounded-full border-4 border-[#0e1530] bg-cyan-900/50 text-2xl font-bold text-cyan-100 shadow-[0_0_18px_rgba(34,211,238,0.25)]">
                                {{ strtoupper(substr($profileUser->username, 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <h1 class="text-2xl font-black uppercase tracking-[0.05em] text-cyan-100">{{ '@'.$profileUser->username }}</h1>
                            <p class="text-sm text-cyan-200/85">{{ $profileUser->profile->bio ?? 'No bio yet.' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @auth
                            @if(auth()->id() !== $profileUser->id)
                                @if($isFollowing)
                                    <form method="POST" action="{{ route('users.unfollow', $profileUser) }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="_redirect" value="1" />
                                        <button type="submit" class="rounded-lg border border-orange-300/60 px-3 py-2 text-xs uppercase tracking-[0.14em] text-orange-200 hover:bg-orange-400/20">
                                            Unfollow
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('users.follow', $profileUser) }}">
                                        @csrf
                                        <input type="hidden" name="_redirect" value="1" />
                                        <button type="submit" class="rounded-lg border border-cyan-300/50 px-3 py-2 text-xs uppercase tracking-[0.14em] text-cyan-200 hover:border-orange-300/70 hover:text-orange-200">
                                            Follow
                                        </button>
                                    </form>
                                @endif
                            @endif

                            @if(auth()->id() === $profileUser->id)
                                <a href="{{ route('profile.edit') }}" class="rounded-lg border border-cyan-300/50 px-3 py-2 text-xs uppercase tracking-[0.14em] text-cyan-200 hover:border-orange-300/70 hover:text-orange-200">
                                    Edit Profile
                                </a>
                            @endif
                        @endauth
                        <a href="{{ route('dashboard') }}" class="rounded-lg border border-cyan-300/50 px-3 py-2 text-xs uppercase tracking-[0.14em] text-cyan-200 hover:border-orange-300/70 hover:text-orange-200">
                            Back to Feed
                        </a>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-3 text-sm text-cyan-200">
                    <button type="button" @click="openList = 'followers'" class="rounded-lg border border-cyan-300/35 px-3 py-1.5 hover:border-orange-300/70 hover:text-orange-200">
                        <span class="font-semibold text-orange-200">{{ $followersCount }}</span> Followers
                    </button>
                    <button type="button" @click="openList = 'following'" class="rounded-lg border border-cyan-300/35 px-3 py-1.5 hover:border-orange-300/70 hover:text-orange-200">
                        <span class="font-semibold text-orange-200">{{ $followingCount }}</span> Following
                    </button>
                    <span class="rounded-lg border border-cyan-300/35 px-3 py-1.5"><span class="font-semibold text-orange-200">{{ $posts->total() }}</span> Posts</span>
                </div>

                @if($profileUser->profile->location || $profileUser->profile->website)
                    <div class="mt-3 text-xs text-cyan-300/90">
                        @if($profileUser->profile->location)
                            <p>Location: {{ $profileUser->profile->location }}</p>
                        @endif
                        @if($profileUser->profile->website)
                            <p>
                                Website:
                                <a href="{{ $profileUser->profile->website }}" target="_blank" class="text-orange-200 underline underline-offset-2">{{ $profileUser->profile->website }}</a>
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        <div x-cloak x-show="openList" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="openList = null">
            <div class="w-full max-w-xl rounded-2xl border border-cyan-300/35 bg-[#0b1328] p-5 shadow-[0_0_30px_rgba(34,211,238,0.2)]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-200" x-text="openList === 'followers' ? 'Followers' : 'Following'"></h3>
                    <button type="button" @click="openList = null" class="rounded-lg border border-orange-300/60 px-3 py-1 text-xs uppercase tracking-[0.14em] text-orange-200">Close</button>
                </div>

                <div class="max-h-96 space-y-2 overflow-auto pr-1">
                    <template x-if="openList === 'followers'">
                        <div>
                            @forelse($followers as $item)
                                <a href="{{ route('profiles.show', ['user' => $item->username]) }}" class="mb-2 flex items-center justify-between rounded-lg border border-cyan-300/25 px-3 py-2 text-sm text-cyan-100 hover:border-orange-300/60 hover:text-orange-200">
                                    <span>{{ '@'.$item->username }}</span>
                                    <span class="text-xs text-cyan-300/80">View</span>
                                </a>
                            @empty
                                <p class="text-sm text-cyan-200/70">No followers yet.</p>
                            @endforelse
                        </div>
                    </template>

                    <template x-if="openList === 'following'">
                        <div>
                            @forelse($following as $item)
                                <a href="{{ route('profiles.show', ['user' => $item->username]) }}" class="mb-2 flex items-center justify-between rounded-lg border border-cyan-300/25 px-3 py-2 text-sm text-cyan-100 hover:border-orange-300/60 hover:text-orange-200">
                                    <span>{{ '@'.$item->username }}</span>
                                    <span class="text-xs text-cyan-300/80">View</span>
                                </a>
                            @empty
                                <p class="text-sm text-cyan-200/70">Not following anyone yet.</p>
                            @endforelse
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @if(session('status'))
        <div class="mt-5 rounded-xl border border-emerald-300/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <section class="mt-6 space-y-4">
        @forelse($posts as $post)
            @php
                $summary = $post->reaction_summary ?? [
                    'like' => 0,
                    'love' => 0,
                    'laugh' => 0,
                    'wow' => 0,
                    'sad' => 0,
                    'angry' => 0,
                ];
            @endphp

            <article class="rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 p-5 shadow-[0_0_20px_rgba(34,211,238,0.15)]" x-data="{ showComments: false, showEditPostForm: false }">
                <p class="text-xs text-cyan-300/80">{{ $post->created_at?->diffForHumans() }}</p>
                <p class="mt-2 whitespace-pre-line text-sm text-cyan-50">{{ $post->content }}</p>

                @if(auth()->id() === $post->user_id)
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <button type="button" @click="showEditPostForm = !showEditPostForm" class="rounded-lg border border-cyan-300/45 px-3 py-1.5 text-[11px] uppercase tracking-[0.14em] text-cyan-200 hover:border-orange-300/70 hover:text-orange-200">
                            Edit
                        </button>

                        <form method="POST" action="{{ route('posts.destroy', $post) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="_redirect" value="1" />
                            <button type="submit" class="rounded-lg border border-rose-300/50 px-3 py-1.5 text-[11px] uppercase tracking-[0.14em] text-rose-200 hover:bg-rose-500/15">
                                Delete
                            </button>
                        </form>
                    </div>

                    <form x-cloak x-show="showEditPostForm" method="POST" action="{{ route('posts.update', $post) }}" class="mt-3 space-y-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_redirect" value="1" />
                        <textarea name="content" rows="3" class="w-full rounded-xl border border-cyan-300/35 bg-[#091226] px-4 py-3 text-sm text-cyan-50 outline-none transition focus:border-orange-300">{{ $post->content }}</textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-lg border border-cyan-300/50 px-3 py-2 text-xs uppercase tracking-[0.14em] text-cyan-200 hover:border-orange-300/70 hover:text-orange-200">
                                Save Post
                            </button>
                        </div>
                    </form>
                @endif

                @include('livewire.components.media-gallery', ['media' => $post->media])

                @include('livewire.components.reaction-bar', [
                    'action' => route('posts.reactions.toggle', $post),
                    'summary' => $summary,
                    'currentReaction' => $post->current_user_reaction ?? null,
                ])

                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-cyan-200/80">
                    <button type="button" @click="showComments = !showComments" class="rounded-lg border border-cyan-300/35 px-3 py-1.5 hover:border-orange-300/70 hover:text-orange-200">
                        {{ $post->comments_count }} comments
                    </button>
                    <span class="rounded-lg border border-cyan-300/25 px-3 py-1.5">{{ $post->reactions_count }} reactions</span>
                </div>

                <div x-cloak x-show="showComments" class="mt-4">
                    @include('livewire.components.comment-thread', [
                        'post' => $post,
                        'comments' => $post->topLevelComments,
                    ])
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 p-6 text-center text-cyan-200/80">
                No posts yet.
            </div>
        @endforelse
    </section>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
@endsection
