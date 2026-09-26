<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $course->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if ($courseCompletion)
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center justify-between">
                    <span class="text-lg font-medium text-green-800">
                        {{ __('You completed this course on :date.', ['date' => $courseCompletion->completed_at->format('Y-m-d')]) }}
                    </span>
                    @if ($certificate)
                        <a href="{{ route('certificates.show', $certificate) }}" target="_blank" class="text-lg font-medium text-indigo-600 hover:text-indigo-800 underline">
                            {{ __('View Certificate') }}
                        </a>
                    @endif
                </div>
            @endif

            @if ($courseProgress !== null)
                <div class="mb-6 max-w-xs">
                    <x-progress-bar :percent="$courseProgress" />
                </div>
            @endif

            <div class="flex gap-4 border-b border-gray-200 mb-6 text-lg font-medium">
                <button wire:click="$set('activeTab', 'content')" class="px-3 py-2 border-b-2 {{ $activeTab === 'content' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-800' }}">
                    {{ __('Course Content') }}
                </button>
                <button wire:click="$set('activeTab', 'announcements')" class="px-3 py-2 border-b-2 {{ $activeTab === 'announcements' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-800' }}">
                    {{ __('Announcements') }}
                </button>
                <button wire:click="$set('activeTab', 'live-classes')" class="px-3 py-2 border-b-2 {{ $activeTab === 'live-classes' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-800' }}">
                    {{ __('Live Classes') }}
                </button>
                <button wire:click="$set('activeTab', 'attendance')" class="px-3 py-2 border-b-2 {{ $activeTab === 'attendance' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-800' }}">
                    {{ __('Attendance') }}
                </button>
                <button wire:click="$set('activeTab', 'assessments')" class="px-3 py-2 border-b-2 {{ $activeTab === 'assessments' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-800' }}">
                    {{ __('Assessments') }}
                </button>
                <a href="{{ route('courses.discussions.index', $course) }}" wire:navigate class="px-3 py-2 border-b-2 border-transparent text-gray-800 hover:text-gray-800">
                    {{ __('Discussions') }}
                </a>
                <button wire:click="$set('activeTab', 'assignments')" class="px-3 py-2 border-b-2 {{ $activeTab === 'assignments' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-800' }}">
                    {{ __('Assignments') }}
                </button>
            </div>

            @if ($activeTab === 'content')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 lg:col-span-1 h-fit">
                        @forelse ($course->sections as $section)
                            <div class="mb-4">
                                <h4 class="text-lg font-semibold text-gray-900 mb-1">{{ $section->title }}</h4>
                                <ul class="space-y-1">
                                    @foreach ($section->lessons as $lesson)
                                        <li>
                                            <button
                                                wire:click="selectLesson({{ $lesson->id }})"
                                                class="w-full text-start text-lg px-2 py-1.5 rounded {{ $selectedLessonId === $lesson->id ? 'bg-gray-900 text-white' : 'text-gray-800 hover:bg-gray-100' }}"
                                            >
                                                {{ $lesson->content_type === 'video' ? '▶' : '▤' }} {{ $lesson->title }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <p class="text-lg text-gray-800">{{ __('No content has been published for this course yet.') }}</p>
                        @endforelse
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-2">
                        @if ($selectedLesson)
                            <h3 class="font-semibold text-lg text-gray-900">{{ $selectedLesson->title }}</h3>
                            @if ($selectedLesson->description)
                                <p class="mt-2 text-lg text-gray-800">{{ $selectedLesson->description }}</p>
                            @endif

                            @if ($selectedLesson->content_type === 'video' && $selectedLesson->video)
                                <div class="mt-4" wire:key="video-{{ $selectedLesson->video->id }}">
                                    <video
                                        controls
                                        class="w-full rounded-lg bg-black"
                                        x-data
                                        x-init="$el.currentTime = {{ $selectedVideoProgress->watched_seconds ?? 0 }}"
                                        @timeupdate.throttle.5000ms="$wire.updateProgress({{ $selectedLesson->video->id }}, Math.floor($el.currentTime), Math.floor($el.duration || 0))"
                                        @ended="$wire.updateProgress({{ $selectedLesson->video->id }}, Math.floor($el.duration || 0), Math.floor($el.duration || 0))"
                                    >
                                        <source src="{{ route('video.stream', $selectedLesson->video) }}">
                                    </video>
                                    <div class="mt-2 text-sm text-gray-800">
                                        {{ __('Progress:') }} {{ $selectedVideoProgress->percentage ?? 0 }}%
                                        @if ($selectedVideoProgress?->completed_at)
                                            &middot; {{ __('Completed') }}
                                        @endif
                                    </div>
                                </div>
                            @elseif ($selectedLesson->content_type === 'video')
                                <p class="mt-4 text-lg text-gray-800">{{ __('The video for this lesson has not been uploaded yet.') }}</p>
                            @endif

                            @if ($selectedLesson->resources->isNotEmpty())
                                <div class="mt-6">
                                    <h4 class="text-lg font-medium text-gray-900">{{ __('Downloadable Resources') }}</h4>
                                    <ul class="mt-2 space-y-1">
                                        @foreach ($selectedLesson->resources as $resource)
                                            <li>
                                                <a href="{{ route('lesson-resources.download', $resource) }}" class="text-lg text-indigo-600 hover:text-indigo-800 underline">
                                                    {{ $resource->original_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @else
                            <p class="text-lg text-gray-800">{{ __('Select a lesson to begin.') }}</p>
                        @endif
                    </div>
                </div>
            @elseif ($activeTab === 'announcements')
                <div class="space-y-3">
                    @forelse ($announcements as $announcement)
                        <div class="bg-white shadow-sm sm:rounded-lg p-5">
                            <div class="flex items-center gap-2">
                                @if ($announcement->is_pinned)
                                    <span class="text-amber-500" title="{{ __('Pinned') }}">&#9733;</span>
                                @endif
                                <h4 class="font-medium text-gray-900">{{ $announcement->title }}</h4>
                            </div>
                            <p class="mt-1 text-lg text-gray-800 whitespace-pre-line">{{ $announcement->body }}</p>
                            <p class="mt-2 text-sm text-gray-600">{{ $announcement->published_at->format('Y-m-d H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-center text-gray-800 py-6">{{ __('No announcements yet.') }}</p>
                    @endforelse
                </div>
            @elseif ($activeTab === 'live-classes')
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Starts') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($liveClasses as $liveClass)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $liveClass->title }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $liveClass->starts_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span @class([
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                            'bg-blue-100 text-blue-800' => $liveClass->status === 'scheduled',
                                            'bg-red-100 text-red-800' => $liveClass->status === 'cancelled',
                                            'bg-gray-100 text-gray-800' => $liveClass->status === 'completed',
                                        ])>
                                            {{ ucfirst($liveClass->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        @if ($liveClass->status === 'scheduled' && $liveClass->isJoinLinkReleased())
                                            <a href="{{ $liveClass->meeting_link }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-800 underline">
                                                {{ __('Join') }}
                                            </a>
                                        @elseif ($liveClass->status === 'scheduled')
                                            <span class="text-sm text-gray-600">{{ __('Link opens shortly before start') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-800">{{ __('No live classes scheduled yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif ($activeTab === 'attendance')
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Live Class') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Date') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Attendance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($liveClasses as $liveClass)
                                @php $record = $attendance->get($liveClass->id); @endphp
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $liveClass->title }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $liveClass->starts_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        @php $status = $record->status ?? 'unmarked'; @endphp
                                        <span @class([
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-medium',
                                            'bg-green-100 text-green-800' => $status === 'present',
                                            'bg-red-100 text-red-800' => $status === 'absent',
                                            'bg-amber-100 text-amber-800' => $status === 'excused',
                                            'bg-gray-100 text-gray-800' => $status === 'unmarked',
                                        ])>
                                            {{ $status === 'unmarked' ? __('Not yet recorded') : ucfirst($status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-800">{{ __('No live classes yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif ($activeTab === 'assessments')
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Type') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($quizzes as $quiz)
                                @php $latest = $latestAttempts->get($quiz->id); @endphp
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $quiz->title }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ ucfirst(str_replace('_', ' ', $quiz->type)) }}</td>
                                    <td class="px-4 py-3 text-gray-800">
                                        @if (! $latest)
                                            {{ __('Not started') }}
                                        @elseif ($latest->status === 'in_progress')
                                            {{ __('In progress') }}
                                        @elseif ($latest->status === 'submitted')
                                            {{ __('Awaiting grading') }}
                                        @else
                                            {{ __('Completed') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="{{ route('my-courses.quizzes.take', $quiz) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                            {{ $latest ? __('View') : __('Start') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-800">{{ __('No assessments published yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif ($activeTab === 'assignments')
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Title') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Due') }}</th>
                                <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($assignments as $assignment)
                                @php $submission = $submissions->get($assignment->id); @endphp
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $assignment->title }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $assignment->due_at?->format('Y-m-d H:i') ?? __('No due date') }}</td>
                                    <td class="px-4 py-3 text-gray-800">
                                        @if (! $submission)
                                            {{ __('Not submitted') }}
                                        @elseif ($submission->status === 'graded')
                                            {{ __('Graded') }}: {{ $submission->score }} / {{ $assignment->max_points }}
                                        @else
                                            {{ __('Awaiting grading') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="{{ route('my-courses.assignments.submit', $assignment) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                            {{ $submission ? __('View') : __('Submit') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-800">{{ __('No assignments published yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
