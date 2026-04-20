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
    $avatarSrc = $avatar
        ? (str_starts_with($avatar, 'http') ? $avatar : asset('storage/'.$avatar))
        : null;
@endphp

<article class="rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 p-5 shadow-[0_0_20px_rgba(34,211,238,0.15)]">
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
                    @{{ $post->author->username }}
                </a>
                <p class="text-xs text-cyan-300/80">{{ $post->created_at?->diffForHumans() }}</p>
            </div>
        </div>

        <span class="rounded-full border border-cyan-300/30 px-3 py-1 text-[11px] uppercase tracking-[0.15em] text-cyan-300">
            {{ $post->comments_count }} comments
        </span>
    </div>

    <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-cyan-50">{{ $post->content }}</p>

    @include('livewire.components.media-gallery', ['media' => $post->media])

    @include('livewire.components.reaction-bar', [
        'action' => route('posts.reactions.toggle', $post),
        'summary' => $summary,
    ])

    @include('livewire.components.comment-thread', [
        'post' => $post,
        'comments' => $post->topLevelComments,
    ])
</article>
