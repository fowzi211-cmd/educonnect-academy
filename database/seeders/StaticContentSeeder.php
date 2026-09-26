<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seeds the CMS-editable content behind the public static pages and FAQ list
 * (spec section "Content Management"). Runs in every environment — without
 * it these routes would render with no content, not demo data they can do
 * without.
 */
class StaticContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPages();
        $this->seedFaqs();
    }

    protected function seedPages(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title_en' => 'About Dr. Nada Center',
                'title_ar' => 'عن مركز د. ندى',
                'body_en' => <<<'HTML'
                    <p>Dr. Nada Center is an online education platform built to connect students with lecturers through scheduled live classes and recorded video lessons. Students subscribe to the courses they need, attend live sessions in their own time zone, and revisit recorded material whenever it suits them.</p>
                    <p>The platform is bilingual from the ground up: every page, form, and dashboard works fully in both Arabic and English, with proper right-to-left layout for Arabic readers.</p>
                    <p>Dr. Nada Center brings together course delivery, assessments, payments, and reporting in a single platform for students, lecturers, and administrators.</p>
                    HTML,
                'body_ar' => <<<'HTML'
                    <p>مركز د. ندى هو منصة تعليم إلكتروني صُممت لربط الطلاب بالمحاضرين من خلال حصص مباشرة مجدولة ودروس فيديو مسجلة. يشترك الطلاب في الدورات التي يحتاجونها، ويحضرون الجلسات المباشرة في المنطقة الزمنية الخاصة بهم، ويعودون إلى المواد المسجلة في أي وقت يناسبهم.</p>
                    <p>المنصة ثنائية اللغة من الأساس: كل صفحة ونموذج ولوحة تحكم تعمل بشكل كامل باللغتين العربية والإنجليزية، مع تخطيط صحيح من اليمين إلى اليسار للقرّاء الناطقين بالعربية.</p>
                    <p>يجمع مركز د. ندى بين تقديم الدورات والتقييمات والمدفوعات والتقارير في منصة واحدة للطلاب والمحاضرين والإداريين.</p>
                    HTML,
            ],
            [
                'slug' => 'how-it-works',
                'title_en' => 'How It Works',
                'title_ar' => 'كيف تعمل المنصة',
                'body_en' => <<<'HTML'
                    <h2>1. Create your account</h2>
                    <p>Register with your name, email, and mobile number, and verify your email address.</p>
                    <h2>2. Find a course</h2>
                    <p>Browse the course catalogue and choose the subject, lecturer, and delivery format that fits you.</p>
                    <h2>3. Subscribe and start learning</h2>
                    <p>Pay a simple monthly subscription, join live classes, and watch recorded lessons at your own pace.</p>
                    <h2>4. Track your progress and get certified</h2>
                    <p>Complete assignments and assessments, follow your progress, and earn a verifiable certificate on completion.</p>
                    HTML,
                'body_ar' => <<<'HTML'
                    <h2>1. أنشئ حسابك</h2>
                    <p>سجّل باستخدام اسمك وبريدك الإلكتروني ورقم جوالك، وتحقق من عنوان بريدك الإلكتروني.</p>
                    <h2>2. ابحث عن دورة</h2>
                    <p>تصفّح دليل الدورات واختر المادة والمحاضر وطريقة التقديم التي تناسبك.</p>
                    <h2>3. اشترك وابدأ التعلم</h2>
                    <p>ادفع اشتراكًا شهريًا بسيطًا، وانضم إلى الحصص المباشرة، وشاهد الدروس المسجلة بالسرعة التي تناسبك.</p>
                    <h2>4. تابع تقدمك واحصل على شهادة</h2>
                    <p>أكمل الواجبات والتقييمات، وتابع تقدمك، واحصل على شهادة قابلة للتحقق عند إتمام الدورة.</p>
                    HTML,
            ],
            [
                'slug' => 'terms',
                'title_en' => 'Terms and Conditions',
                'title_ar' => 'الشروط والأحكام',
                'body_en' => <<<'HTML'
                    <p>These Terms and Conditions govern your use of Dr. Nada Center. By creating an account, you agree to these terms.</p>
                    <h2>1. Accounts</h2>
                    <p>You must provide accurate registration information and keep your password secure. You are responsible for all activity under your account.</p>
                    <h2>2. Course Subscriptions</h2>
                    <p>Paid courses are billed as recurring monthly subscriptions unless stated otherwise on the course page. Access to course content is granted only after a subscription payment is confirmed.</p>
                    <h2>3. Acceptable Use</h2>
                    <p>You may not share your account, redistribute course recordings, or use the platform for any unlawful purpose. Lecturers may not contact students outside of their enrolled courses without authorisation.</p>
                    <h2>4. Cancellations and Refunds</h2>
                    <p>Cancellation and refund terms are described in our Refund and Cancellation Policy.</p>
                    <h2>5. Changes to These Terms</h2>
                    <p>We may update these terms as the platform evolves. Material changes will be reflected in the version number, and continued use of the platform after a change constitutes acceptance.</p>
                    <h2>6. Contact</h2>
                    <p>Questions about these terms can be sent through our Contact page.</p>
                    HTML,
                'body_ar' => <<<'HTML'
                    <p>تحكم هذه الشروط والأحكام استخدامك لمركز د. ندى. بإنشائك حسابًا، فإنك توافق على هذه الشروط.</p>
                    <h2>1. الحسابات</h2>
                    <p>يجب عليك تقديم معلومات تسجيل دقيقة والحفاظ على سرية كلمة المرور الخاصة بك. أنت مسؤول عن جميع الأنشطة التي تتم من خلال حسابك.</p>
                    <h2>2. اشتراكات الدورات</h2>
                    <p>تُفوتر الدورات المدفوعة كاشتراكات شهرية متكررة ما لم يُذكر خلاف ذلك في صفحة الدورة. يُمنح الوصول إلى محتوى الدورة فقط بعد تأكيد دفع الاشتراك.</p>
                    <h2>3. الاستخدام المقبول</h2>
                    <p>لا يجوز لك مشاركة حسابك، أو إعادة توزيع تسجيلات الدورات، أو استخدام المنصة لأي غرض غير قانوني. لا يجوز للمحاضرين التواصل مع الطلاب خارج الدورات المسجَّلين فيها دون تصريح.</p>
                    <h2>4. الإلغاء والاسترداد</h2>
                    <p>تُوصف شروط الإلغاء والاسترداد في سياسة الاسترداد والإلغاء الخاصة بنا.</p>
                    <h2>5. تغييرات على هذه الشروط</h2>
                    <p>قد نحدّث هذه الشروط مع تطور المنصة. ستنعكس التغييرات الجوهرية في رقم الإصدار، ويشكّل استمرار استخدام المنصة بعد أي تغيير قبولًا له.</p>
                    <h2>6. التواصل</h2>
                    <p>يمكن إرسال الأسئلة حول هذه الشروط من خلال صفحة التواصل الخاصة بنا.</p>
                    HTML,
            ],
            [
                'slug' => 'privacy',
                'title_en' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'body_en' => <<<'HTML'
                    <p>This policy explains what personal data Dr. Nada Center collects, why, and how you can control it.</p>
                    <h2>1. What We Collect</h2>
                    <p>We collect the information you provide when registering (name, email, mobile number, country, preferred language) and, optionally, demographic details you choose to add to your profile. We also record course activity such as attendance and progress needed to run the platform.</p>
                    <h2>2. How We Use It</h2>
                    <p>Your data is used to operate your account, deliver course content you are enrolled in, process payments, send necessary transactional notifications, and improve the platform. We do not sell your personal data.</p>
                    <h2>3. Your Choices</h2>
                    <p>You can update your profile information at any time, change your notification preferences, and request a copy or deletion of your data subject to legal retention requirements, by contacting us.</p>
                    <h2>4. Data Retention and Security</h2>
                    <p>We keep personal data only as long as needed for the purposes described here or as required by law, and apply reasonable technical and organisational measures to protect it.</p>
                    <h2>5. Cookies</h2>
                    <p>Details on the cookies we use are set out in our Cookie Policy.</p>
                    <h2>6. Contact</h2>
                    <p>For any privacy question or request, please use our Contact page.</p>
                    HTML,
                'body_ar' => <<<'HTML'
                    <p>توضح هذه السياسة البيانات الشخصية التي يجمعها مركز د. ندى، ولماذا، وكيف يمكنك التحكم بها.</p>
                    <h2>1. ما الذي نجمعه</h2>
                    <p>نجمع المعلومات التي تقدمها عند التسجيل (الاسم، البريد الإلكتروني، رقم الجوال، الدولة، اللغة المفضلة)، وبشكل اختياري، أي تفاصيل ديموغرافية تختار إضافتها إلى ملفك الشخصي. كما نسجل نشاط الدورة مثل الحضور والتقدم اللازمين لتشغيل المنصة.</p>
                    <h2>2. كيف نستخدمها</h2>
                    <p>تُستخدم بياناتك لتشغيل حسابك، وتقديم محتوى الدورات المسجَّل فيها، ومعالجة المدفوعات، وإرسال الإشعارات المعاملاتية الضرورية، وتحسين المنصة. نحن لا نبيع بياناتك الشخصية.</p>
                    <h2>3. خياراتك</h2>
                    <p>يمكنك تحديث معلومات ملفك الشخصي في أي وقت، وتغيير تفضيلات الإشعارات، وطلب نسخة من بياناتك أو حذفها وفقًا لمتطلبات الاحتفاظ القانونية، من خلال التواصل معنا.</p>
                    <h2>4. الاحتفاظ بالبيانات وأمانها</h2>
                    <p>نحتفظ بالبيانات الشخصية فقط طالما كانت ضرورية للأغراض الموضحة هنا أو كما يقتضي القانون، ونطبق إجراءات تقنية وتنظيمية معقولة لحمايتها.</p>
                    <h2>5. ملفات تعريف الارتباط</h2>
                    <p>تفاصيل ملفات تعريف الارتباط التي نستخدمها موضحة في سياسة ملفات تعريف الارتباط الخاصة بنا.</p>
                    <h2>6. التواصل</h2>
                    <p>لأي سؤال أو طلب متعلق بالخصوصية، يُرجى استخدام صفحة التواصل الخاصة بنا.</p>
                    HTML,
            ],
            [
                'slug' => 'refund-policy',
                'title_en' => 'Refund and Cancellation Policy',
                'title_ar' => 'سياسة الاسترداد والإلغاء',
                'body_en' => <<<'HTML'
                    <h2>1. Cancelling a Subscription</h2>
                    <p>You may cancel a course subscription at any time from your dashboard. Cancelling stops future renewal but does not end access early — you keep access until the end of the period you already paid for, unless a refund is issued.</p>
                    <h2>2. Refund Eligibility</h2>
                    <p>Refund eligibility (such as a trial period or a short cooling-off window after a first payment) is set per course and shown on the course page before you subscribe. Refunds outside that window are reviewed case by case.</p>
                    <h2>3. How Refunds Are Processed</h2>
                    <p>Approved refunds are returned to the original payment method. Processing times depend on the payment provider and are typically a few business days.</p>
                    <h2>4. Administrative Cancellation</h2>
                    <p>We may suspend or cancel access in cases of policy violation, fraud, or non-payment, in accordance with these terms.</p>
                    <h2>5. Requesting a Refund</h2>
                    <p>To request a refund, contact us through the Contact page with your account email and the course in question.</p>
                    HTML,
                'body_ar' => <<<'HTML'
                    <h2>1. إلغاء الاشتراك</h2>
                    <p>يمكنك إلغاء اشتراك الدورة في أي وقت من لوحة التحكم الخاصة بك. يوقف الإلغاء التجديد المستقبلي لكنه لا ينهي الوصول مبكرًا — تحتفظ بالوصول حتى نهاية الفترة التي دفعت مقابلها بالفعل، ما لم يُصدر استرداد.</p>
                    <h2>2. أهلية الاسترداد</h2>
                    <p>تُحدَّد أهلية الاسترداد (مثل فترة تجريبية أو فترة قصيرة لإعادة النظر بعد أول دفعة) لكل دورة على حدة وتُعرض في صفحة الدورة قبل الاشتراك. تُراجَع طلبات الاسترداد خارج تلك الفترة حالة بحالة.</p>
                    <h2>3. كيفية معالجة الاسترداد</h2>
                    <p>تُعاد المبالغ المستردة المعتمدة إلى وسيلة الدفع الأصلية. تعتمد مدة المعالجة على مزود الدفع وعادة ما تستغرق بضعة أيام عمل.</p>
                    <h2>4. الإلغاء الإداري</h2>
                    <p>يجوز لنا تعليق أو إلغاء الوصول في حالات مخالفة السياسة أو الاحتيال أو عدم الدفع، وفقًا لهذه الشروط.</p>
                    <h2>5. طلب استرداد</h2>
                    <p>لطلب استرداد، تواصل معنا عبر صفحة التواصل مع ذكر بريد حسابك والدورة المعنية.</p>
                    HTML,
            ],
            [
                'slug' => 'cookie-policy',
                'title_en' => 'Cookie Policy',
                'title_ar' => 'سياسة ملفات تعريف الارتباط',
                'body_en' => <<<'HTML'
                    <p>Dr. Nada Center uses a small number of cookies to run the platform.</p>
                    <h2>Essential Cookies</h2>
                    <p>These keep you signed in, remember your chosen interface language, and protect forms from cross-site request forgery. The platform cannot function without them, so they cannot be turned off.</p>
                    <h2>Optional Cookies</h2>
                    <p>As features such as analytics are added, any optional cookies will be listed here along with a way to opt out, in line with our Privacy Policy.</p>
                    <h2>Managing Cookies</h2>
                    <p>Most browsers let you block or delete cookies in their settings. Blocking essential cookies will prevent you from staying signed in.</p>
                    HTML,
                'body_ar' => <<<'HTML'
                    <p>يستخدم مركز د. ندى عددًا محدودًا من ملفات تعريف الارتباط لتشغيل المنصة.</p>
                    <h2>ملفات تعريف الارتباط الأساسية</h2>
                    <p>تُبقيك مسجلاً للدخول، وتتذكر لغة الواجهة التي اخترتها، وتحمي النماذج من هجمات التزوير عبر المواقع. لا يمكن للمنصة العمل بدونها، لذا لا يمكن إيقافها.</p>
                    <h2>ملفات تعريف الارتباط الاختيارية</h2>
                    <p>مع إضافة ميزات مثل التحليلات، سيتم إدراج أي ملفات تعريف ارتباط اختيارية هنا مع طريقة لإلغاء الاشتراك فيها، بما يتماشى مع سياسة الخصوصية الخاصة بنا.</p>
                    <h2>إدارة ملفات تعريف الارتباط</h2>
                    <p>تسمح لك معظم المتصفحات بحظر أو حذف ملفات تعريف الارتباط من إعداداتها. حظر ملفات تعريف الارتباط الأساسية سيمنعك من البقاء مسجلاً للدخول.</p>
                    HTML,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }

    protected function seedFaqs(): void
    {
        $faqs = [
            [
                'question_en' => 'Do I need to pay to create an account?',
                'question_ar' => 'هل أحتاج إلى الدفع لإنشاء حساب؟',
                'answer_en' => 'No. Creating an account is free. You only pay when you subscribe to a course.',
                'answer_ar' => 'لا. إنشاء الحساب مجاني. تدفع فقط عند الاشتراك في دورة.',
                'position' => 1,
            ],
            [
                'question_en' => 'What languages is the platform available in?',
                'question_ar' => 'بأي لغات تتوفر المنصة؟',
                'answer_en' => 'Dr. Nada Center is available in both Arabic and English. You can switch languages at any time from the header.',
                'answer_ar' => 'يتوفر مركز د. ندى باللغتين العربية والإنجليزية. يمكنك تبديل اللغة في أي وقت من أعلى الصفحة.',
                'position' => 2,
            ],
            [
                'question_en' => 'Can I access recorded lessons after I finish a course?',
                'question_ar' => 'هل يمكنني الوصول إلى الدروس المسجلة بعد إنهاء الدورة؟',
                'answer_en' => 'Access to recorded content after completion depends on the settings each course is published with; this will be shown clearly on the course page.',
                'answer_ar' => 'يعتمد الوصول إلى المحتوى المسجل بعد الإتمام على الإعدادات التي نُشرت بها كل دورة؛ وسيظهر ذلك بوضوح في صفحة الدورة.',
                'position' => 3,
            ],
            [
                'question_en' => 'What currency are prices shown in?',
                'question_ar' => 'بأي عملة تُعرض الأسعار؟',
                'answer_en' => 'Prices are shown in Sudanese Pound (SDG) by default. The platform is built to support additional currencies in the future.',
                'answer_ar' => 'تُعرض الأسعار بالجنيه السوداني افتراضيًا. المنصة مبنية لدعم عملات إضافية مستقبلاً.',
                'position' => 4,
            ],
            [
                'question_en' => 'How do I contact support?',
                'question_ar' => 'كيف يمكنني التواصل مع الدعم؟',
                'answer_en' => 'Use the Contact page to send us a message, or open a support ticket once you have an account.',
                'answer_ar' => 'استخدم صفحة التواصل لإرسال رسالة إلينا، أو افتح تذكرة دعم بمجرد امتلاكك حسابًا.',
                'position' => 5,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['question_en' => $faq['question_en']], $faq);
        }
    }
}
