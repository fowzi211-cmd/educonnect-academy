<x-public-layout :title="$title" :description="__('Learn about EduConnect Academy, a bilingual platform connecting students and lecturers.')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('About EduConnect Academy') }}</h1>

        <div class="mt-6 space-y-4 text-gray-700 leading-relaxed">
            <p>{{ __('EduConnect Academy is an online education platform built to connect students with lecturers through scheduled live classes and recorded video lessons. Students subscribe to the courses they need, attend live sessions in their own time zone, and revisit recorded material whenever it suits them.') }}</p>
            <p>{{ __('The platform is bilingual from the ground up: every page, form, and dashboard works fully in both Arabic and English, with proper right-to-left layout for Arabic readers.') }}</p>
            <p>{{ __('We are currently building EduConnect Academy in phases — starting with accounts, roles, and the platform foundation, then layering on course delivery, assessments, payments, and reporting as each phase is completed and tested.') }}</p>
        </div>
    </div>
</x-public-layout>
