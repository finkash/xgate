@extends('layouts.neon')

@section('title', '@'.$profileUser->username.' | '.config('app.name', 'xgate'))

@section('content')
    @php
        $cover = $profileUser->profile->cover_path ?? null;
        $coverSrc = $cover ? (str_starts_with($cover, 'http') ? $cover : asset('storage/'.$cover)) : null;
        $avatar = $profileUser->profile->avatar_path ?? null;
        $avatarSrc = $avatar ? (str_starts_with($avatar, 'http') ? $avatar : asset('storage/'.$avatar)) : null;
    @endphp

    <section class="overflow-hidden rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 shadow-[0_0_20px_rgba(34,211,238,0.15)]">
        <div class="h-44 w-full bg-[#091226]">
            @if($coverSrc)
                <img src="{{ $coverSrc }}" alt="cover" class="h-full w-full object-cover" />
            @endif
        </div>

        <div class="p-5">
            <div class="-mt-14 flex flex-wrap items-end justify-between gap-4">
                <div class="flex items-end gap-4">
                    @if($avatarSrc)
                        <img src="{{ $avatarSrc }}" alt="avatar" class="h-24 w-24 rounded-full border-4 border-[#0e1530] object-cover" />
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-[#0e1530] bg-cyan-900/50 text-2xl font-bold text-cyan-100">
                            {{ strtoupper(substr($profileUser->username, 0, 1)) }}
                        </div>
                    @endif

                    <div>
                        <h1 class="text-2xl font-black uppercase tracking-[0.05em] text-cyan-100">@{{ $profileUser->username }}</h1>
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

            <div class="mt-4 flex gap-5 text-sm text-cyan-200">
                <p><span class="font-semibold text-orange-200">{{ $followersCount }}</span> Followers</p>
                <p><span class="font-semibold text-orange-200">{{ $followingCount }}</span> Following</p>
                <p><span class="font-semibold text-orange-200">{{ $posts->total() }}</span> Posts</p>
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

    @if(session('status'))
        <div class="mt-5 rounded-xl border border-emerald-300/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <section class="mt-6 space-y-4">
        @forelse($posts as $post)
            <article class="rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 p-5 shadow-[0_0_20px_rgba(34,211,238,0.15)]">
                <p class="text-xs text-cyan-300/80">{{ $post->created_at?->diffForHumans() }}</p>
                <p class="mt-2 whitespace-pre-line text-sm text-cyan-50">{{ $post->content }}</p>

                @include('livewire.components.media-gallery', ['media' => $post->media])

                <div class="mt-4 flex gap-4 text-xs text-cyan-200/80">
                    <span>{{ $post->comments_count }} comments</span>
                    <span>{{ $post->reactions_count }} reactions</span>
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
