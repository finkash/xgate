@extends('layouts.neon')

@section('title', 'Feed | '.config('app.name', 'xgate'))

@section('content')
    <header class="mb-6 rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 p-5 shadow-[0_0_20px_rgba(34,211,238,0.15)]">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.32em] text-orange-300">xgate social</p>
                <h1 class="mt-1 text-3xl font-black uppercase tracking-[0.06em] text-cyan-200">Neon Feed</h1>
                <p class="mt-2 text-sm text-cyan-100/80">
                    Mode: <span class="font-semibold text-orange-200">{{ $feedMode === 'following' ? 'Following' : 'Discover' }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('profiles.show', ['user' => auth()->user()->username]) }}" class="rounded-lg border border-cyan-300/45 px-3 py-2 text-xs uppercase tracking-[0.14em] text-cyan-200 hover:border-orange-300/70 hover:text-orange-200">
                    My Profile
                </a>
                <a href="{{ route('profile.edit') }}" class="rounded-lg border border-cyan-300/45 px-3 py-2 text-xs uppercase tracking-[0.14em] text-cyan-200 hover:border-orange-300/70 hover:text-orange-200">
                    Edit Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-orange-300/60 bg-orange-400/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-orange-200 hover:bg-orange-400/20">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </header>

    @if(session('status'))
        <div class="mb-5 rounded-xl border border-emerald-300/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-rose-300/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="mb-6 rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 p-5 shadow-[0_0_20px_rgba(34,211,238,0.15)]">
        <h2 class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-cyan-300">Create Post</h2>
        <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="hidden" name="_redirect" value="1" />
            <textarea name="content" rows="3" class="w-full rounded-xl border border-cyan-300/35 bg-[#091226] px-4 py-3 text-sm text-cyan-50 outline-none transition focus:border-orange-300" placeholder="Share something...">{{ old('content') }}</textarea>
            <div class="flex flex-wrap items-center gap-3">
                <input type="file" name="media[]" multiple class="rounded-lg border border-cyan-300/30 bg-[#091226] px-3 py-2 text-xs text-cyan-100" />
                <button type="submit" class="rounded-lg border border-orange-300/60 bg-gradient-to-r from-orange-400 to-pink-500 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-slate-950 shadow-[0_0_20px_rgba(251,146,60,0.35)]">
                    Post
                </button>
            </div>
        </form>
    </section>

    <section class="space-y-5">
        @forelse($feed as $post)
            @include('livewire.components.post-card', ['post' => $post])
        @empty
            <div class="rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 p-8 text-center text-cyan-200/90">
                Your feed is empty. Try following users or creating your first post.
            </div>
        @endforelse
    </section>

    <div class="mt-6">
        {{ $feed->links() }}
    </div>
@endsection
