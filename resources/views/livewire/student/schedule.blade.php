<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Schedule') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if ($events->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center text-gray-800 italic">
                    {{ __("You have no upcoming live classes or exam windows. Check back once your lecturers schedule some.") }}
                </div>
            @else
                @foreach ($events->groupBy(fn ($event) => $event['when']->toDateString()) as $date => $dayEvents)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            {{ \Illuminate\Support\Carbon::parse($date)->isToday() ? __('Today') : (\Illuminate\Support\Carbon::parse($date)->isTomorrow() ? __('Tomorrow') : \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, j F Y')) }}
                        </h3>

                        <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100">
                            @foreach ($dayEvents as $event)
                                <div class="p-4 flex items-center gap-4">
                                    <div @class([
                                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-full',
                                        'bg-indigo-50 text-indigo-600' => $event['type'] === 'live_class',
                                        'bg-amber-50 text-amber-600' => $event['type'] !== 'live_class',
                                    ])>
                                        @if ($event['type'] === 'live_class')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-2.36a.75.75 0 011.03.67v6.38a.75.75 0 01-1.03.67l-4.72-2.36m-15 3.75h9a1.5 1.5 0 001.5-1.5v-9a1.5 1.5 0 00-1.5-1.5h-9a1.5 1.5 0 00-1.5 1.5v9a1.5 1.5 0 001.5 1.5z" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ $event['title'] }}</p>
                                        <p class="text-sm text-gray-600">
                                            {{ $event['course']->title }}
                                            &middot;
                                            {{ $event['when']->format('H:i') }}
                                            @if ($event['type'] === 'live_class')
                                                &ndash; {{ $event['model']->ends_at->format('H:i') }}
                                            @elseif ($event['type'] === 'exam_opens')
                                                &middot; {{ __('opens') }}
                                            @else
                                                &middot; {{ __('closes') }}
                                            @endif
                                        </p>
                                    </div>

                                    @if ($event['type'] === 'live_class')
                                        @if ($event['model']->isJoinLinkReleased())
                                            <a href="{{ $event['model']->meeting_link }}" target="_blank" rel="noopener" class="shrink-0 rounded-lg px-3 py-1.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700">
                                                {{ __('Join') }}
                                            </a>
                                        @else
                                            <a href="{{ route('my-courses.classroom', $event['course']) }}" wire:navigate class="shrink-0 text-sm text-indigo-600 hover:text-indigo-800 underline">
                                                {{ __('View classroom') }}
                                            </a>
                                        @endif
                                    @elseif ($event['type'] === 'exam_opens')
                                        <a href="{{ route('my-courses.quizzes.take', $event['model']) }}" wire:navigate class="shrink-0 text-sm text-indigo-600 hover:text-indigo-800 underline">
                                            {{ __('View details') }}
                                        </a>
                                    @else
                                        <a href="{{ route('my-courses.quizzes.take', $event['model']) }}" wire:navigate class="shrink-0 rounded-lg px-3 py-1.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700">
                                            {{ __('Take') }}
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
</div>
