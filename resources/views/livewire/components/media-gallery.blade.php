@php
    $items = collect($media ?? []);
    $count = $items->count();

    if ($count <= 1) {
        $gridClass = 'grid-cols-1';
    } elseif ($count === 2) {
        $gridClass = 'grid-cols-2';
    } else {
        $gridClass = 'grid-cols-2 sm:grid-cols-3';
    }
@endphp

@if($count > 0)
    <div class="mt-4 grid {{ $gridClass }} gap-2">
        @foreach($items as $item)
            @php
                $typeValue = $item->type instanceof \App\Domain\Content\Enums\MediaType ? $item->type->value : (string) $item->type;
                $src = str_starts_with((string) $item->file_path, 'http') ? $item->file_path : asset('storage/'.$item->file_path);
            @endphp

            <div class="overflow-hidden rounded-xl border border-cyan-300/30 bg-[#0b1328]">
                @if($typeValue === 'video')
                    <video controls class="h-56 w-full object-cover" preload="metadata">
                        <source src="{{ $src }}" />
                    </video>
                @else
                    <img src="{{ $src }}" alt="{{ $item->alt_text ?? 'Post media' }}" class="h-56 w-full object-cover" />
                @endif
            </div>
        @endforeach
    </div>
@endif
