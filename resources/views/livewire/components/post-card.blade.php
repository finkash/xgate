@props(['post'])

@php
    $summary = $post->reaction_summary ?? [
        'like' => 0,
        'love' => 0,
        'laugh' => 0,
        'wow' => 0,
        'sad' => 0,
        'angry' => 0,
    ];

    $avatar = $post->author->profile->avatar_path ?? null;
    if ($avatar && str_starts_with($avatar, 'http')) {
        $avatarSrc = $avatar;
    } elseif ($avatar) {
        $avatarSrc = route('media.show', ['path' => ltrim($avatar, '/')]);
    } else {
        $avatarSrc = null;
    }
@endphp

<article class="rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 p-5 shadow-[0_0_20px_rgba(34,211,238,0.15)]" x-data="{ showEditPostForm: false }">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            @if($avatarSrc)
                <img src="{{ $avatarSrc }}" alt="avatar" class="h-10 w-10 rounded-full border border-cyan-300/40 object-cover" />
            @else
                <div class="flex h-10 w-10 items-center justify-center rounded-full border border-cyan-300/40 bg-cyan-900/40 text-xs uppercase text-cyan-200">
                    {{ strtoupper(substr($post->author->username ?? 'U', 0, 1)) }}
                </div>
            @endif
            <div>
                <a href="{{ route('profiles.show', ['user' => $post->author->username]) }}" class="text-sm font-semibold text-cyan-100 hover:text-orange-200">
                    {{ '@'.$post->author->username }}
                </a>
                <p class="text-xs text-cyan-300/80">{{ $post->created_at?->diffForHumans() }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="rounded-full border border-cyan-300/30 px-3 py-1 text-[11px] uppercase tracking-[0.15em] text-cyan-300">
                {{ $post->comments_count }} comments
            </span>

            @if(auth()->id() === $post->user_id)
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
            @endif
        </div>
    </div>

    @if(auth()->id() === $post->user_id)
        <form x-cloak x-show="showEditPostForm" method="POST" action="{{ route('posts.update', $post) }}" class="mt-4 space-y-2">
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

    <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-cyan-50">{{ $post->content }}</p>

    @include('livewire.components.media-gallery', ['media' => $post->media])

    @include('livewire.components.reaction-bar', [
        'action' => route('posts.reactions.toggle', $post),
        'summary' => $summary,
        'currentReaction' => $post->current_user_reaction ?? null,
    ])

    @include('livewire.components.comment-thread', [
        'post' => $post,
        'comments' => $post->topLevelComments,
    ])
</article>
