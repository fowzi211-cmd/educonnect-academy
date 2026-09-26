<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance') }}: {{ $liveClass->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <p class="text-lg text-gray-800 mb-6">{{ $liveClass->starts_at->format('Y-m-d H:i') }}</p>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Student') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-800">{{ __('Mark') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($students as $student)
                            @php $record = $attendance->get($student->id); @endphp
                            <tr wire:key="student-{{ $student->id }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $student->name }}</div>
                                    <div class="text-gray-800">{{ $student->email }}</div>
                                </td>
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
                                <td class="px-4 py-3 space-x-2 rtl:space-x-reverse">
                                    <button wire:click="mark({{ $student->id }}, 'present')" class="text-green-700 hover:text-green-900 underline">{{ __('Present') }}</button>
                                    <button wire:click="mark({{ $student->id }}, 'absent')" class="text-red-700 hover:text-red-900 underline">{{ __('Absent') }}</button>
                                    <button wire:click="mark({{ $student->id }}, 'excused')" class="text-amber-700 hover:text-amber-900 underline">{{ __('Excused') }}</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-800">{{ __('No enrolled students yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
