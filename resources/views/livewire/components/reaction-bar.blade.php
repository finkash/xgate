@props([
    'action',
    'summary' => [],
])

@php
    $types = ['like', 'love', 'laugh', 'wow', 'sad', 'angry'];
@endphp

<div class="mt-4 flex flex-wrap gap-2">
    @foreach($types as $type)
        <form method="POST" action="{{ $action }}">
            @csrf
            <input type="hidden" name="_redirect" value="1" />
            <input type="hidden" name="type" value="{{ $type }}" />
            <button
                type="submit"
                class="rounded-lg border border-cyan-300/35 bg-[#091226] px-3 py-1.5 text-xs uppercase tracking-[0.16em] text-cyan-100 transition hover:border-orange-300/60 hover:text-orange-200"
            >
                {{ $type }} · {{ $summary[$type] ?? 0 }}
            </button>
        </form>
    @endforeach
</div>
