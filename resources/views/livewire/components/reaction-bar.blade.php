@props([
    'action',
    'summary' => [],
    'currentReaction' => null,
])

@php
    $types = ['like', 'love', 'laugh', 'wow', 'sad', 'angry'];
    $normalized = [];
    foreach ($types as $type) {
        $normalized[$type] = (int) ($summary[$type] ?? 0);
    }
@endphp

<div
    class="mt-4 flex flex-wrap items-center gap-2"
    x-data="reactionBar({
        action: @js($action),
        summary: @js($normalized),
        currentReaction: @js($currentReaction),
        token: @js(csrf_token())
    })"
>
    @foreach($types as $type)
        <button
            type="button"
            @click="toggle('{{ $type }}')"
            :disabled="loading"
            :class="isActive('{{ $type }}')
                ? 'border-orange-300/80 text-orange-200 bg-orange-400/10'
                : 'border-cyan-300/35 text-cyan-100 bg-[#091226]'"
            class="inline-flex min-w-[6.4rem] items-center justify-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-[11px] uppercase tracking-[0.08em] leading-none transition hover:border-orange-300/60 hover:text-orange-200"
        >
            <span class="w-4 text-center" x-text="icons['{{ $type }}']"></span>
            <span>{{ strtoupper($type) }}</span>
            <span class="tabular-nums" x-text="summary['{{ $type }}']"></span>
        </button>
    @endforeach
</div>
