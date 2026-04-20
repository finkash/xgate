@php
    $items = collect($media ?? [])->values();
    $count = $items->count();

    $prepared = $items->map(function ($item) {
        $typeValue = $item->type instanceof \App\Domain\Content\Enums\MediaType ? $item->type->value : (string) $item->type;

        if (str_starts_with((string) $item->file_path, 'http')) {
            $src = $item->file_path;
        } else {
            $src = route('media.show', ['path' => ltrim((string) $item->file_path, '/')]);
        }

        return [
            'type' => $typeValue,
            'src' => $src,
            'alt' => $item->alt_text ?? 'Post media',
        ];
    })->all();
@endphp

@if($count > 0)
    <div
        class="mt-4"
        x-data="{
            open: false,
            index: 0,
            items: @js($prepared),
            canPrev() { return this.index > 0; },
            canNext() { return this.index < (this.items.length - 1); },
            next() {
                if (this.canNext()) {
                    this.index += 1;
                }
            },
            prev() {
                if (this.canPrev()) {
                    this.index -= 1;
                }
            },
            show(i) { this.index = i; this.open = true; },
        }"
    >
        <template x-if="items.length === 1">
            <div class="overflow-hidden rounded-xl border border-cyan-300/30 bg-[#0b1328]">
                <button type="button" class="block w-full" @click="show(0)">
                    <template x-if="items[0].type === 'video'">
                        <video controls class="h-[26rem] w-full bg-black object-contain" preload="metadata">
                            <source :src="items[0].src" />
                        </video>
                    </template>
                    <template x-if="items[0].type !== 'video'">
                        <div class="relative h-[26rem] w-full overflow-hidden bg-[#050a18]">
                            <img :src="items[0].src" :alt="items[0].alt" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-40 blur-lg" />
                            <img :src="items[0].src" :alt="items[0].alt" class="relative z-10 h-full w-full object-contain" />
                        </div>
                    </template>
                </button>
            </div>
        </template>

        <template x-if="items.length > 1">
            <div class="relative overflow-hidden rounded-xl border border-cyan-300/30 bg-[#0b1328]">
                <div class="block w-full">
                    <template x-if="items[index].type === 'video'">
                        <video controls class="h-[26rem] w-full bg-black object-contain" preload="metadata">
                            <source :src="items[index].src" />
                        </video>
                    </template>
                    <template x-if="items[index].type !== 'video'">
                        <button type="button" class="relative h-[26rem] w-full overflow-hidden bg-[#050a18]" @click="show(index)">
                            <img :src="items[index].src" :alt="items[index].alt" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-40 blur-lg" />
                            <img :src="items[index].src" :alt="items[index].alt" class="relative z-10 h-full w-full object-contain" />
                        </button>
                    </template>
                </div>

                <button x-cloak x-show="canPrev()" type="button" @click.prevent.stop="prev" class="absolute left-3 top-1/2 z-20 -translate-y-1/2 rounded-md border border-cyan-300/45 bg-[#091226]/85 px-2 py-1 text-lg text-cyan-100 shadow-[0_0_14px_rgba(34,211,238,0.18)] transition hover:border-orange-300/70 hover:text-orange-200">&lsaquo;</button>
                <button x-cloak x-show="canNext()" type="button" @click.prevent.stop="next" class="absolute right-3 top-1/2 z-20 -translate-y-1/2 rounded-md border border-cyan-300/45 bg-[#091226]/85 px-2 py-1 text-lg text-cyan-100 shadow-[0_0_14px_rgba(34,211,238,0.18)] transition hover:border-orange-300/70 hover:text-orange-200">&rsaquo;</button>
            </div>
        </template>

        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4" @click.self="open = false">
            <div class="relative w-full max-w-5xl rounded-2xl border border-cyan-300/35 bg-[#0b1328] p-4 shadow-[0_0_30px_rgba(34,211,238,0.2)]">
                <button type="button" @click="open = false" class="absolute right-3 top-3 px-2 py-1 text-[10px] uppercase tracking-[0.14em] text-orange-200/90 hover:text-orange-100">Close</button>

                <div class="mt-6 overflow-hidden rounded-xl border border-cyan-300/25 bg-black">
                    <template x-if="items[index].type === 'video'">
                        <video controls class="max-h-[80vh] w-full object-contain" preload="metadata">
                            <source :src="items[index].src" />
                        </video>
                    </template>
                    <template x-if="items[index].type !== 'video'">
                        <img :src="items[index].src" :alt="items[index].alt" class="max-h-[80vh] w-full object-contain" />
                    </template>
                </div>

                <template x-if="items.length > 1">
                    <div class="mt-4 grid grid-cols-3 items-center">
                        <div>
                            <button x-cloak x-show="canPrev()" type="button" @click="prev" class="rounded-lg border border-cyan-300/50 px-3 py-2 text-xs uppercase tracking-[0.14em] text-cyan-100 hover:border-orange-300/70 hover:text-orange-200">Prev</button>
                        </div>
                        <p class="text-center text-xs text-cyan-200/80" x-text="`${index + 1} / ${items.length}`"></p>
                        <div class="text-right">
                            <button x-cloak x-show="canNext()" type="button" @click="next" class="rounded-lg border border-cyan-300/50 px-3 py-2 text-xs uppercase tracking-[0.14em] text-cyan-100 hover:border-orange-300/70 hover:text-orange-200">Next</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
@endif
