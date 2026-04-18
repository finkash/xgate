<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign In | {{ config('app.name', 'xgate') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-hidden bg-[#070b19] text-cyan-100">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_10%_20%,rgba(255,113,91,0.15),transparent_35%),radial-gradient(circle_at_90%_15%,rgba(34,211,238,0.2),transparent_30%),radial-gradient(circle_at_50%_100%,rgba(74,222,128,0.14),transparent_38%)]"></div>
        <div class="pointer-events-none absolute -left-24 top-20 h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-16 bottom-10 h-80 w-80 rounded-full bg-orange-400/20 blur-3xl"></div>

        <main class="relative flex min-h-screen items-center justify-center px-6 py-12">
            <div class="w-full max-w-md rounded-3xl border border-cyan-300/40 bg-[#0e1530]/85 p-8 shadow-[0_0_18px_rgba(34,211,238,0.35),0_0_35px_rgba(249,115,22,0.20)] backdrop-blur">
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.45em] text-orange-300">Hello!</p>
                <h1 class="mb-6 text-3xl font-black uppercase tracking-[0.06em] text-cyan-200">Sign In</h1>

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-300/45 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.perform') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="mb-1 block text-xs uppercase tracking-[0.24em] text-cyan-300">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border border-cyan-200/35 bg-[#091226] px-4 py-3 text-cyan-50 outline-none transition focus:border-orange-300 focus:shadow-[0_0_20px_rgba(251,146,60,0.3)]" />
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-xs uppercase tracking-[0.24em] text-cyan-300">Password</label>
                        <input id="password" type="password" name="password" required class="w-full rounded-xl border border-cyan-200/35 bg-[#091226] px-4 py-3 text-cyan-50 outline-none transition focus:border-orange-300 focus:shadow-[0_0_20px_rgba(251,146,60,0.3)]" />
                    </div>

                    <label class="flex items-center gap-3 text-sm text-cyan-200/85">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-cyan-300/50 bg-[#091226] text-orange-400 focus:ring-orange-300" />
                        Keep me signed in
                    </label>

                    <button type="submit" class="w-full rounded-xl border border-orange-300/60 bg-gradient-to-r from-orange-400 to-pink-500 px-4 py-3 text-sm font-bold uppercase tracking-[0.25em] text-slate-950 shadow-[0_0_20px_rgba(251,146,60,0.5)] transition hover:scale-[1.01]">
                        Enter
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-cyan-100/90">
                    No account yet?
                    <a href="{{ route('register') }}" class="font-semibold text-orange-300 underline decoration-orange-300/50 underline-offset-4 transition hover:text-orange-200">Create one</a>
                </p>
            </div>
        </main>
    </body>
</html>
