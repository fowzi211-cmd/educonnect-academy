<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lecturer Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex gap-2 text-sm">
                @foreach (['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected'), 'all' => __('All')] as $value => $label)
                    <button
                        wire:click="$set('status', '{{ $value }}')"
                        class="px-3 py-1.5 rounded-md border {{ $status === $value ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Applicant') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Headline') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-500">{{ __('Submitted') }}</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($applications as $application)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $application->user->name }}</div>
                                    <div class="text-gray-500">{{ $application->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $application->headline }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-amber-100 text-amber-800' => $application->status === 'pending',
                                        'bg-green-100 text-green-800' => $application->status === 'approved',
                                        'bg-red-100 text-red-800' => $application->status === 'rejected',
                                    ])>
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $application->created_at->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('admin.lecturer-applications.show', $application) }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 underline">
                                        {{ __('Review') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">{{ __('No applications found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</div>
