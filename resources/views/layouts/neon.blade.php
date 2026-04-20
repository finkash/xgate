<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name', 'xgate'))</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#070b19] text-cyan-100">
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(circle_at_10%_20%,rgba(255,113,91,0.15),transparent_35%),radial-gradient(circle_at_90%_15%,rgba(34,211,238,0.2),transparent_30%),radial-gradient(circle_at_50%_100%,rgba(74,222,128,0.14),transparent_38%)]"></div>
        <div class="pointer-events-none fixed -left-24 top-20 h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl"></div>
        <div class="pointer-events-none fixed -right-16 bottom-10 h-80 w-80 rounded-full bg-orange-400/20 blur-3xl"></div>

        <main class="relative z-10 mx-auto min-h-screen max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </body>
</html>
