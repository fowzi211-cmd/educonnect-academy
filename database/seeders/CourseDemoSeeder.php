<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\LecturerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseDemoSeeder extends Seeder
{
    /**
     * Demo categories, approved lecturer profiles, and courses spanning every
     * status (spec section 33: courses in various states for a realistic demo).
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@educonnect.test')->first();

        $categories = collect([
            'Public Health' => 'Courses about public health and epidemiology.',
            'Mathematics' => 'From arithmetic foundations to advanced calculus.',
            'Computer Science' => 'Programming, algorithms, and software development.',
            'Business' => 'Management, communication, and entrepreneurship.',
            'Languages' => 'Language learning for academic and professional use.',
        ])->mapWithKeys(fn ($description, $name) => [
            $name => Category::firstOrCreate(['name' => $name], ['description' => $description]),
        ]);

        $lecturerBios = [
            'lecturer1@educonnect.test' => [
                'headline' => 'Mathematics Lecturer',
                'biography' => 'Fifteen years of experience teaching mathematics at secondary and university level.',
                'qualifications' => 'Ph.D. in Mathematics, King Abdulaziz University.',
                'areas_of_expertise' => 'Statistics, Calculus, Linear Algebra',
            ],
            'lecturer2@educonnect.test' => [
                'headline' => 'Software Engineering Lecturer',
                'biography' => 'Former software engineer turned educator, focused on practical, project-based programming courses.',
                'qualifications' => 'M.Sc. in Computer Science, KFUPM.',
                'areas_of_expertise' => 'Python, Web Development, Algorithms',
            ],
            'lecturer3@educonnect.test' => [
                'headline' => 'Business Communication Lecturer',
                'biography' => 'Corporate trainer and lecturer specialising in professional communication and language skills.',
                'qualifications' => 'M.A. in Applied Linguistics, King Saud University.',
                'areas_of_expertise' => 'Business Communication, Arabic as a Second Language',
            ],
        ];

        $lecturers = [];

        foreach ($lecturerBios as $email => $bio) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                continue;
            }

            LecturerProfile::firstOrCreate(
                ['user_id' => $user->id],
                array_merge($bio, [
                    'status' => LecturerProfile::STATUS_APPROVED,
                    'reviewed_by' => $admin?->id,
                    'reviewed_at' => now(),
                ])
            );

            $lecturers[$email] = $user;
        }

        if (count($lecturers) < 3) {
            return;
        }

        $courses = [
            [
                'title' => 'Introduction to Statistics',
                'short_description' => 'Core statistical concepts for everyday decision making.',
                'full_description' => "Learn how to summarise data, understand probability, and interpret results with confidence. Includes weekly live problem-solving sessions and recorded lectures you can revisit anytime.",
                'category' => 'Mathematics',
                'lecturer' => 'lecturer1@educonnect.test',
                'level' => 'beginner',
                'teaching_language' => 'en',
                'delivery_format' => 'blended',
                'monthly_price' => 59.00,
                'certificate_available' => true,
                'status' => Course::STATUS_PUBLISHED,
            ],
            [
                'title' => 'Python for Beginners',
                'short_description' => 'Start programming with Python, no prior experience required.',
                'full_description' => "A hands-on introduction to Python: variables, control flow, functions, and small real-world projects. Recorded lessons with optional live office hours.",
                'category' => 'Computer Science',
                'lecturer' => 'lecturer2@educonnect.test',
                'level' => 'beginner',
                'teaching_language' => 'en',
                'delivery_format' => 'recorded',
                'monthly_price' => 79.00,
                'trial_period_days' => 7,
                'certificate_available' => true,
                'status' => Course::STATUS_PUBLISHED,
            ],
            [
                'title' => 'Business Communication Skills',
                'short_description' => 'Write and speak more effectively in professional settings.',
                'full_description' => "Covers email etiquette, presentations, negotiation language, and meeting facilitation for Arabic and English-speaking professionals.",
                'category' => 'Business',
                'lecturer' => 'lecturer3@educonnect.test',
                'level' => 'intermediate',
                'teaching_language' => 'both',
                'delivery_format' => 'live',
                'monthly_price' => null,
                'status' => Course::STATUS_UNDER_REVIEW,
                'submitted_at' => now(),
            ],
            [
                'title' => 'Advanced Calculus',
                'short_description' => 'Multivariable calculus for engineering and science students.',
                'full_description' => "Partial derivatives, multiple integrals, and vector calculus, building on a first course in calculus.",
                'category' => 'Mathematics',
                'lecturer' => 'lecturer1@educonnect.test',
                'level' => 'advanced',
                'teaching_language' => 'en',
                'delivery_format' => 'recorded',
                'monthly_price' => 89.00,
                'status' => Course::STATUS_DRAFT,
            ],
            [
                'title' => 'Public Speaking Essentials',
                'short_description' => 'Build confidence presenting to any audience.',
                'full_description' => "Practical techniques for structuring talks, managing nerves, and engaging an audience, with recorded practice sessions.",
                'category' => 'Business',
                'lecturer' => 'lecturer2@educonnect.test',
                'level' => 'beginner',
                'teaching_language' => 'en',
                'delivery_format' => 'blended',
                'monthly_price' => 39.00,
                'status' => Course::STATUS_REVISION_REQUESTED,
                'submitted_at' => now(),
                'revision_notes' => 'Please add a clearer breakdown of what each module covers before resubmitting.',
            ],
            [
                'title' => 'Arabic for Non-Native Speakers',
                'short_description' => 'Modern Standard Arabic for adult learners.',
                'full_description' => "Reading, writing, and conversational Arabic from the ground up, paced for working professionals.",
                'category' => 'Languages',
                'lecturer' => 'lecturer3@educonnect.test',
                'level' => 'beginner',
                'teaching_language' => 'ar',
                'delivery_format' => 'blended',
                'monthly_price' => 49.00,
                'certificate_available' => true,
                'status' => Course::STATUS_APPROVED,
                'submitted_at' => now(),
            ],
        ];

        foreach ($courses as $data) {
            if (Course::where('title', $data['title'])->exists()) {
                continue;
            }

            $lecturer = $lecturers[$data['lecturer']];

            $course = Course::create([
                'title' => $data['title'],
                'short_description' => $data['short_description'],
                'full_description' => $data['full_description'],
                'category_id' => $categories[$data['category']]->id,
                'level' => $data['level'],
                'teaching_language' => $data['teaching_language'],
                'delivery_format' => $data['delivery_format'],
                'monthly_price' => $data['monthly_price'] ?? null,
                'trial_period_days' => $data['trial_period_days'] ?? null,
                'certificate_available' => $data['certificate_available'] ?? false,
                'currency' => 'SAR',
                'status' => $data['status'],
                'submitted_at' => $data['submitted_at'] ?? null,
                'published_at' => $data['status'] === Course::STATUS_PUBLISHED ? now() : null,
                'revision_notes' => $data['revision_notes'] ?? null,
                'created_by' => $lecturer->id,
            ]);

            $course->lecturers()->attach($lecturer->id, ['is_primary' => true]);
        }
    }
}
