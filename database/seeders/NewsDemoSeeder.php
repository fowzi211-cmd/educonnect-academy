<?php

namespace Database\Seeders;

use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Sample headlines for the home-page news carousel — dev/demo only, so a
 * fresh production deploy starts with an empty carousel rather than fake
 * announcements. Idempotent via firstOrCreate on the title.
 */
class NewsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@educonnect.test')->first();

        $items = [
            [
                'title_en' => 'Bank Transfer Now Available',
                'title_ar' => 'التحويل البنكي متاح الآن',
                'excerpt_en' => 'Students can now pay for course subscriptions by bank transfer — upload your receipt and our team reviews it within one business day.',
                'excerpt_ar' => 'يمكن للطلاب الآن الدفع مقابل اشتراكات الدورات عبر التحويل البنكي — ارفع إيصالك وسيقوم فريقنا بمراجعته خلال يوم عمل واحد.',
            ],
            [
                'title_en' => 'New Courses Added This Month',
                'title_ar' => 'دورات جديدة أُضيفت هذا الشهر',
                'excerpt_en' => 'Explore fresh courses across mathematics, programming, and business communication, taught live and available as recorded lessons.',
                'excerpt_ar' => 'استكشف دورات جديدة في الرياضيات والبرمجة والتواصل التجاري، تُقدَّم مباشرة ومتوفرة كدروس مسجلة.',
            ],
            [
                'title_en' => 'Certificates Now Verifiable Online',
                'title_ar' => 'الشهادات قابلة للتحقق عبر الإنترنت الآن',
                'excerpt_en' => 'Every certificate issued on the platform can now be verified instantly using its certificate number — share yours with confidence.',
                'excerpt_ar' => 'يمكن الآن التحقق من كل شهادة تصدرها المنصة فورًا باستخدام رقم الشهادة — شارك شهادتك بثقة.',
            ],
        ];

        foreach ($items as $index => $item) {
            NewsItem::firstOrCreate(
                ['title_en' => $item['title_en']],
                [
                    ...$item,
                    'position' => $index + 1,
                    'is_published' => true,
                    'created_by' => $admin?->id,
                ]
            );
        }
    }
}
