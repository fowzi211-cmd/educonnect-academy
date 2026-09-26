<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Our Lecturers') }}</h1>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($lecturers as $lecturer)
                <a href="{{ route('lecturers.show', $lecturer->user) }}" wire:navigate class="block bg-white border border-gray-100 rounded-lg p-5 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center gap-3">
                        @if ($lecturer->photo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($lecturer->photo_path) }}" alt="{{ $lecturer->user->name }}" class="h-12 w-12 rounded-full object-cover">
                        @else
                            <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-medium">
                                {{ mb_substr($lecturer->user->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $lecturer->user->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $lecturer->headline }}</p>
                        </div>
                    </div>
                    @if ($lecturer->areas_of_expertise)
                        <p class="mt-3 text-sm text-gray-600">{{ $lecturer->areas_of_expertise }}</p>
                    @endif
                </a>
            @empty
                <p class="col-span-full text-center text-gray-600 py-12">{{ __('No approved lecturers yet.') }}</p>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $lecturers->links() }}
        </div>
    </div>
</div>
