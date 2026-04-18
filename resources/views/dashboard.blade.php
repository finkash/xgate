<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard | {{ config('app.name', 'xgate') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-cyan-50">
        <div class="mx-auto flex min-h-screen max-w-4xl items-center px-6 py-16">
            <div class="w-full rounded-3xl border border-cyan-300/30 bg-slate-900/80 p-8 shadow-[0_0_25px_rgba(34,211,238,0.22)] backdrop-blur">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-cyan-300">xgate</p>
                        <h1 class="mt-2 text-4xl font-black uppercase tracking-[0.08em] text-orange-300">Hello World</h1>
                        <p class="mt-3 text-cyan-100/85">Welcome back, {{ auth()->user()->name }}.</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-orange-300/60 bg-orange-400/15 px-5 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-orange-200 transition hover:bg-orange-400/25">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
