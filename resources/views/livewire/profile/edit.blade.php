@extends('layouts.neon')

@section('title', 'Edit Profile | '.config('app.name', 'xgate'))

@section('content')
    <section class="mx-auto max-w-3xl rounded-2xl border border-cyan-300/30 bg-[#0e1530]/90 p-6 shadow-[0_0_20px_rgba(34,211,238,0.15)]">
        <div class="mb-5 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.28em] text-orange-300">xgate profile</p>
                <h1 class="mt-1 text-2xl font-black uppercase tracking-[0.06em] text-cyan-200">Edit Profile</h1>
            </div>
            <a href="{{ route('profiles.show', ['user' => $profileUser->username]) }}" class="rounded-lg border border-cyan-300/50 px-3 py-2 text-xs uppercase tracking-[0.14em] text-cyan-200 hover:border-orange-300/70 hover:text-orange-200">
                View Profile
            </a>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-rose-300/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-xs uppercase tracking-[0.2em] text-cyan-300">Bio</label>
                <textarea name="bio" rows="4" class="w-full rounded-xl border border-cyan-300/35 bg-[#091226] px-4 py-3 text-sm text-cyan-50 outline-none focus:border-orange-300">{{ old('bio', $profileUser->profile->bio ?? '') }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs uppercase tracking-[0.2em] text-cyan-300">Location</label>
                    <input type="text" name="location" value="{{ old('location', $profileUser->profile->location ?? '') }}" class="w-full rounded-xl border border-cyan-300/35 bg-[#091226] px-4 py-3 text-sm text-cyan-50 outline-none focus:border-orange-300" />
                </div>

                <div>
                    <label class="mb-1 block text-xs uppercase tracking-[0.2em] text-cyan-300">Website</label>
                    <input type="url" name="website" value="{{ old('website', $profileUser->profile->website ?? '') }}" class="w-full rounded-xl border border-cyan-300/35 bg-[#091226] px-4 py-3 text-sm text-cyan-50 outline-none focus:border-orange-300" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs uppercase tracking-[0.2em] text-cyan-300">Avatar</label>
                    <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-xl border border-cyan-300/35 bg-[#091226] px-4 py-3 text-xs text-cyan-100" />
                </div>

                <div>
                    <label class="mb-1 block text-xs uppercase tracking-[0.2em] text-cyan-300">Cover</label>
                    <input type="file" name="cover" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-xl border border-cyan-300/35 bg-[#091226] px-4 py-3 text-xs text-cyan-100" />
                </div>
            </div>

            <button type="submit" class="rounded-xl border border-orange-300/60 bg-gradient-to-r from-orange-400 to-pink-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.2em] text-slate-950 shadow-[0_0_20px_rgba(251,146,60,0.35)]">
                Save Profile
            </button>
        </form>
    </section>
@endsection
