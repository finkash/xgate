<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign Up | {{ config('app.name', 'xgate') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-hidden bg-[#060916] text-cyan-100">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_15%_15%,rgba(56,189,248,0.2),transparent_35%),radial-gradient(circle_at_85%_20%,rgba(251,146,60,0.18),transparent_33%),radial-gradient(circle_at_50%_100%,rgba(236,72,153,0.14),transparent_38%)]"></div>
        <div class="pointer-events-none absolute left-0 top-1/3 h-80 w-80 -translate-x-1/2 rounded-full bg-cyan-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute right-0 top-10 h-96 w-96 translate-x-1/3 rounded-full bg-orange-400/20 blur-3xl"></div>

        <main class="relative flex min-h-screen items-center justify-center px-6 py-12">
            <div class="w-full max-w-md rounded-3xl border border-orange-200/35 bg-[#0b1328]/90 p-8 shadow-[0_0_22px_rgba(56,189,248,0.3),0_0_34px_rgba(251,146,60,0.25)] backdrop-blur">
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.45em] text-cyan-300">Welcome!</p>
                <h1 class="mb-6 text-3xl font-black uppercase tracking-[0.06em] text-orange-200">Sign Up</h1>

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-300/45 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.perform') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="username" class="mb-1 block text-xs uppercase tracking-[0.24em] text-cyan-300">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required class="w-full rounded-xl border border-cyan-200/35 bg-[#091226] px-4 py-3 text-cyan-50 outline-none transition focus:border-orange-300 focus:shadow-[0_0_20px_rgba(251,146,60,0.3)]" />
                    </div>

                    <div>
                        <label for="email" class="mb-1 block text-xs uppercase tracking-[0.24em] text-cyan-300">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-cyan-200/35 bg-[#091226] px-4 py-3 text-cyan-50 outline-none transition focus:border-orange-300 focus:shadow-[0_0_20px_rgba(251,146,60,0.3)]" />
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-xs uppercase tracking-[0.24em] text-cyan-300">Password</label>
                        <input id="password" type="password" name="password" required class="w-full rounded-xl border border-cyan-200/35 bg-[#091226] px-4 py-3 text-cyan-50 outline-none transition focus:border-orange-300 focus:shadow-[0_0_20px_rgba(251,146,60,0.3)]" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1 block text-xs uppercase tracking-[0.24em] text-cyan-300">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full rounded-xl border border-cyan-200/35 bg-[#091226] px-4 py-3 text-cyan-50 outline-none transition focus:border-orange-300 focus:shadow-[0_0_20px_rgba(251,146,60,0.3)]" />
                    </div>

                    <button type="submit" class="w-full rounded-xl border border-cyan-300/60 bg-gradient-to-r from-cyan-300 to-orange-400 px-4 py-3 text-sm font-bold uppercase tracking-[0.25em] text-slate-950 shadow-[0_0_20px_rgba(56,189,248,0.45)] transition hover:scale-[1.01]">
                        Create Account
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-cyan-100/90">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-semibold text-orange-300 underline decoration-orange-300/50 underline-offset-4 transition hover:text-orange-200">Sign in</a>
                </p>
            </div>
        </main>
    </body>
</html>
