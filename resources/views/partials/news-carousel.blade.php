@if ($newsItems->isNotEmpty())
    <section class="bg-white border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">{{ __('Latest News') }}</h2>
                @if ($newsItems->count() > 1)
                    <span class="text-xs font-medium text-gray-500">{{ __('Updates automatically') }}</span>
                @endif
            </div>

            <div
                x-data="{
                    slides: {{ $newsItems->count() }},
                    active: 0,
                    timer: null,
                    start() {
                        if (this.slides <= 1) return;
                        this.timer = setInterval(() => { this.active = (this.active + 1) % this.slides; }, 6000);
                    },
                    stop() { clearInterval(this.timer); },
                    goTo(i) { this.active = i; this.stop(); this.start(); },
                    next() { this.goTo((this.active + 1) % this.slides); },
                    prev() { this.goTo((this.active - 1 + this.slides) % this.slides); },
                }"
                x-init="start()"
                @mouseenter="stop()"
                @mouseleave="start()"
                class="relative overflow-hidden rounded-2xl border border-gray-100 shadow-sm bg-white"
            >
                <div class="relative" style="min-height: 220px;">
                    @foreach ($newsItems as $index => $item)
                        <div
                            x-show="active === {{ $index }}"
                            x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            @if ($index !== 0) style="display: none;" @endif
                            class="grid grid-cols-1 sm:grid-cols-2"
                        >
                            @if ($item->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="{{ $item->title() }}" class="h-48 sm:h-full w-full object-cover">
                            @else
                                <div class="h-48 sm:h-full w-full bg-brand-gradient flex items-center justify-center opacity-90">
                                    <svg class="h-10 w-10 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="p-6 sm:p-8 flex flex-col justify-center">
                                <span class="text-xs font-semibold text-brand-primary uppercase tracking-wide">{{ $item->created_at->translatedFormat('M j, Y') }}</span>
                                <h3 class="mt-2 text-xl font-bold text-gray-900">{{ $item->title() }}</h3>
                                <p class="mt-2 text-sm text-gray-600 line-clamp-3">{{ $item->excerpt() }}</p>
                                @if ($item->link_url)
                                    <a href="{{ $item->link_url }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-primary hover:underline">
                                        {{ __('Read more') }}
                                        <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($newsItems->count() > 1)
                    <button @click="prev()" type="button" aria-label="{{ __('Previous') }}" class="absolute inset-y-0 start-0 flex items-center px-2 text-white/90 hover:text-white">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-black/25 hover:bg-black/40 transition">
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </span>
                    </button>
                    <button @click="next()" type="button" aria-label="{{ __('Next') }}" class="absolute inset-y-0 end-0 flex items-center px-2 text-white/90 hover:text-white">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-black/25 hover:bg-black/40 transition">
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </button>

                    <div class="absolute bottom-3 inset-x-0 flex justify-center gap-1.5">
                        @foreach ($newsItems as $index => $item)
                            <button
                                @click="goTo({{ $index }})"
                                type="button"
                                aria-label="{{ __('Go to slide') }} {{ $index + 1 }}"
                                class="h-1.5 rounded-full transition-all"
                                :class="active === {{ $index }} ? 'w-6 bg-brand-primary' : 'w-1.5 bg-gray-300 hover:bg-gray-400'"
                            ></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
