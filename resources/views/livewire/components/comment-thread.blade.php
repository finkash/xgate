@props([
    'post',
    'comments' => [],
])

<div class="mt-5 rounded-xl border border-cyan-300/25 bg-[#0a1022] p-4">
    <h4 class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-cyan-300">Comments</h4>

    <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mb-4 space-y-2">
        @csrf
        <input type="hidden" name="_redirect" value="1" />
        <textarea name="content" rows="2" class="w-full rounded-lg border border-cyan-300/35 bg-[#091226] px-3 py-2 text-sm text-cyan-50 outline-none focus:border-orange-300" placeholder="Write a comment..."></textarea>
        <button type="submit" class="rounded-lg border border-orange-300/55 bg-orange-400/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-orange-200 transition hover:bg-orange-400/20">
            Add Comment
        </button>
    </form>

    <div class="space-y-4">
        @forelse($comments as $comment)
            <article class="rounded-lg border border-cyan-300/20 bg-[#0b1328] p-3">
                <p class="text-xs text-cyan-300">@{{ $comment->user->username ?? 'user' }}</p>
                <p class="mt-1 text-sm text-cyan-50">{{ $comment->content }}</p>

                @include('livewire.components.reaction-bar', [
                    'action' => route('comments.reactions.toggle', [$post, $comment]),
                    'summary' => $comment->reaction_summary ?? [],
                ])

                <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mt-3 space-y-2">
                    @csrf
                    <input type="hidden" name="_redirect" value="1" />
                    <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}" />
                    <textarea name="content" rows="2" class="w-full rounded-lg border border-cyan-300/30 bg-[#091226] px-3 py-2 text-sm text-cyan-50 outline-none focus:border-orange-300" placeholder="Reply..."></textarea>
                    <button type="submit" class="rounded-lg border border-cyan-300/50 px-3 py-1.5 text-xs uppercase tracking-[0.16em] text-cyan-200 hover:border-orange-300/70 hover:text-orange-200">
                        Reply
                    </button>
                </form>

                @if(auth()->id() === $comment->user_id)
                    <div class="mt-3 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('posts.comments.update', [$post, $comment]) }}" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="_redirect" value="1" />
                            <input type="text" name="content" value="{{ $comment->content }}" class="w-full rounded-lg border border-cyan-300/30 bg-[#091226] px-3 py-2 text-xs text-cyan-50 outline-none focus:border-orange-300" />
                        </form>
                        <form method="POST" action="{{ route('posts.comments.destroy', [$post, $comment]) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="_redirect" value="1" />
                            <button type="submit" class="rounded-lg border border-rose-300/50 px-3 py-2 text-xs uppercase tracking-[0.15em] text-rose-200 hover:bg-rose-500/15">
                                Delete
                            </button>
                        </form>
                    </div>
                @endif

                @if($comment->replies->isNotEmpty())
                    <div class="mt-3 space-y-2 border-l border-cyan-300/20 pl-3">
                        @foreach($comment->replies as $reply)
                            <div class="rounded-lg border border-cyan-300/15 bg-[#0a1022] p-2">
                                <p class="text-[11px] text-cyan-300">@{{ $reply->user->username ?? 'user' }}</p>
                                <p class="mt-1 text-xs text-cyan-50">{{ $reply->content }}</p>

                                @include('livewire.components.reaction-bar', [
                                    'action' => route('comments.reactions.toggle', [$post, $reply]),
                                    'summary' => $reply->reaction_summary ?? [],
                                ])
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        @empty
            <p class="text-sm text-cyan-200/80">No comments yet. Start the conversation.</p>
        @endforelse
    </div>
</div>
