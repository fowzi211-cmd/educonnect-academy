<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * The header search box shown on every page. Matches against a
 * permission-aware directory of the app's own pages (so "settings" or
 * "receipts" jumps straight to the right screen), published courses,
 * lecturer profiles, and — for admins holding "manage users" — accounts.
 * Guests only ever see public pages, courses, and lecturers.
 */
class GlobalSearch extends Component
{
    public string $query = '';

    /**
     * Every navigable destination, each guarded the same way the nav links
     * are: 'auth' entries need a login, 'can' entries need that permission.
     * Labels run through __() so matching works in the visitor's language;
     * keywords catch common alternate words in both languages.
     */
    protected function pageDirectory(): array
    {
        return [
            ['label' => 'Home', 'route' => route('home'), 'keywords' => 'main start الرئيسية'],
            ['label' => 'Courses', 'route' => route('courses.index'), 'keywords' => 'catalogue browse learn الدورات'],
            ['label' => 'Lecturers', 'route' => route('lecturers.index'), 'keywords' => 'teachers instructors المحاضرون'],
            ['label' => 'About', 'route' => route('about'), 'keywords' => 'حول'],
            ['label' => 'How It Works', 'route' => route('how-it-works'), 'keywords' => 'كيف تعمل'],
            ['label' => 'FAQ', 'route' => route('faq'), 'keywords' => 'questions الأسئلة الشائعة'],
            ['label' => 'Contact', 'route' => route('contact'), 'keywords' => 'support email اتصل تواصل'],
            ['label' => 'Terms and Conditions', 'route' => route('terms'), 'keywords' => 'الشروط والأحكام'],
            ['label' => 'Privacy Policy', 'route' => route('privacy'), 'keywords' => 'سياسة الخصوصية'],
            ['label' => 'Verify Certificate', 'route' => route('certificates.verify'), 'keywords' => 'certificate check التحقق من الشهادة'],

            ['label' => 'Dashboard', 'route' => route('dashboard'), 'auth' => true, 'keywords' => 'لوحة التحكم'],
            ['label' => 'Profile', 'route' => route('profile'), 'auth' => true, 'keywords' => 'account password الملف الشخصي'],
            ['label' => 'My Learning', 'route' => route('my-courses.index'), 'auth' => true, 'keywords' => 'classroom تعلمي دوراتي'],
            ['label' => 'My Subscriptions', 'route' => route('my-subscriptions.index'), 'auth' => true, 'keywords' => 'billing payments اشتراكاتي'],
            ['label' => 'My Certificates', 'route' => route('my-certificates.index'), 'auth' => true, 'keywords' => 'شهاداتي'],
            ['label' => 'Support', 'route' => route('support-tickets.index'), 'auth' => true, 'keywords' => 'help ticket الدعم'],

            ['label' => 'My Courses', 'route' => route('lecturer.courses.index'), 'can' => 'manage own courses', 'keywords' => 'teaching دوراتي'],
            ['label' => 'My Earnings', 'route' => route('lecturer.earnings'), 'can' => 'manage own courses', 'keywords' => 'payout commission أرباحي'],

            ['label' => 'Users', 'route' => route('admin.users.index'), 'can' => 'manage users', 'keywords' => 'accounts roles المستخدمون'],
            ['label' => 'Course Reviews', 'route' => route('admin.courses.index'), 'can' => 'review courses', 'keywords' => 'approve publish مراجعة الدورات'],
            ['label' => 'Lecturer Applications', 'route' => route('admin.lecturer-applications.index'), 'can' => 'manage lecturer applications', 'keywords' => 'طلبات المحاضرين'],
            ['label' => 'Categories', 'route' => route('admin.categories'), 'can' => 'manage categories', 'keywords' => 'subjects التصنيفات المواد'],
            ['label' => 'Lecture Rooms', 'route' => route('admin.lecture-rooms'), 'can' => 'manage categories', 'keywords' => 'rooms classrooms قاعات المحاضرات'],
            ['label' => 'Enrolments', 'route' => route('admin.enrolments'), 'can' => 'manage enrolments', 'keywords' => 'التسجيلات'],
            ['label' => 'Transactions', 'route' => route('admin.finance.transactions'), 'can' => 'manage finances', 'keywords' => 'finance payments receipts refunds المعاملات المالية'],
            ['label' => 'Invoices', 'route' => route('admin.finance.invoices'), 'can' => 'manage finances', 'keywords' => 'الفواتير'],
            ['label' => 'Revenue Report', 'route' => route('admin.finance.revenue'), 'can' => 'manage finances', 'keywords' => 'تقرير الإيرادات'],
            ['label' => 'Coupons', 'route' => route('admin.finance.coupons'), 'can' => 'manage finances', 'keywords' => 'discount الكوبونات'],
            ['label' => 'Bank Accounts', 'route' => route('admin.finance.bank-accounts'), 'can' => 'manage finances', 'keywords' => 'transfer الحسابات البنكية'],
            ['label' => 'Payouts', 'route' => route('admin.finance.payouts'), 'can' => 'manage lecturer commissions', 'keywords' => 'المدفوعات'],
            ['label' => 'Commissions', 'route' => route('admin.finance.commissions'), 'can' => 'manage lecturer commissions', 'keywords' => 'العمولات'],
            ['label' => 'Certificates', 'route' => route('admin.certificates'), 'can' => 'manage certificates', 'keywords' => 'الشهادات'],
            ['label' => 'Support Tickets', 'route' => route('admin.support-tickets.index'), 'can' => 'manage support tickets', 'keywords' => 'تذاكر الدعم'],
            ['label' => 'Reports', 'route' => route('admin.reports'), 'can' => 'view reports', 'keywords' => 'export csv التقارير'],
            ['label' => 'Content', 'route' => route('admin.pages'), 'can' => 'manage content', 'keywords' => 'cms pages faq المحتوى'],
            ['label' => 'News', 'route' => route('admin.news'), 'can' => 'manage content', 'keywords' => 'carousel homepage announcements الأخبار'],
            ['label' => 'Audit Logs', 'route' => route('admin.audit-logs'), 'can' => 'view audit logs', 'keywords' => 'history سجلات التدقيق'],
            ['label' => 'Settings', 'route' => route('admin.settings'), 'can' => 'manage settings', 'keywords' => 'branding gateway الإعدادات'],
        ];
    }

    protected function matchingPages(string $needle): array
    {
        $user = Auth::user();

        return collect($this->pageDirectory())
            ->filter(function (array $page) use ($user) {
                if (($page['auth'] ?? false) && ! $user) {
                    return false;
                }

                if (isset($page['can']) && (! $user || ! $user->can($page['can']))) {
                    return false;
                }

                return true;
            })
            ->filter(fn (array $page) => str_contains(mb_strtolower(__($page['label'])), $needle)
                || str_contains(mb_strtolower($page['label']), $needle)
                || str_contains(mb_strtolower($page['keywords'] ?? ''), $needle))
            ->take(6)
            ->values()
            ->all();
    }

    public function render()
    {
        $needle = mb_strtolower(trim($this->query));
        $active = mb_strlen($needle) >= 2;

        $courses = $active
            ? Course::where('status', Course::STATUS_PUBLISHED)
                ->where('title', 'like', '%'.$needle.'%')
                ->orderBy('title')
                ->limit(5)
                ->get(['id', 'title', 'slug'])
            : collect();

        $lecturers = $active
            ? User::role('lecturer')->where('name', 'like', '%'.$needle.'%')->orderBy('name')->limit(3)->get(['id', 'name'])
            : collect();

        $users = $active && Auth::user()?->can('manage users')
            ? User::where(fn ($q) => $q->where('name', 'like', '%'.$needle.'%')->orWhere('email', 'like', '%'.$needle.'%'))
                ->orderBy('name')
                ->limit(4)
                ->get(['id', 'name', 'email'])
            : collect();

        return view('livewire.global-search', [
            'pages' => $active ? $this->matchingPages($needle) : [],
            'courses' => $courses,
            'lecturers' => $lecturers,
            'users' => $users,
            'active' => $active,
        ]);
    }
}
