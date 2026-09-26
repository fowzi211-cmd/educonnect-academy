<?php

use App\Http\Controllers\Admin\LecturerDocumentController;
use App\Http\Controllers\AssignmentAttachmentController;
use App\Http\Controllers\AssignmentSubmissionController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Lecturer\QuizSubmissionController;
use App\Http\Controllers\LessonResourceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PublicCourseController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SupportTicketAttachmentController;
use App\Http\Controllers\TransactionReceiptController;
use App\Http\Controllers\VideoStreamController;
use App\Http\Controllers\Webhooks\PayPalWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Livewire\Admin\AuditLogs as AdminAuditLogs;
use App\Livewire\Admin\Categories as AdminCategories;
use App\Livewire\Admin\Certificates as AdminCertificates;
use App\Livewire\Admin\CourseReview;
use App\Livewire\Admin\CourseReviewQueue;
use App\Livewire\Admin\Enrolments as AdminEnrolments;
use App\Livewire\Admin\Faqs as AdminFaqs;
use App\Livewire\Admin\Finance\BankAccounts as AdminFinanceBankAccounts;
use App\Livewire\Admin\Finance\Commissions as AdminFinanceCommissions;
use App\Livewire\Admin\Finance\Coupons as AdminFinanceCoupons;
use App\Livewire\Admin\Finance\Invoices as AdminFinanceInvoices;
use App\Livewire\Admin\Finance\Payouts as AdminFinancePayouts;
use App\Livewire\Admin\Finance\RevenueReport as AdminFinanceRevenueReport;
use App\Livewire\Admin\Finance\Transactions as AdminFinanceTransactions;
use App\Livewire\Admin\LecturerApplicationReview;
use App\Livewire\Admin\LecturerApplications;
use App\Livewire\Admin\LectureRooms as AdminLectureRooms;
use App\Livewire\Admin\News as AdminNews;
use App\Livewire\Admin\Pages as AdminPages;
use App\Livewire\Admin\Reports as AdminReports;
use App\Livewire\Admin\Settings as AdminSettings;
use App\Livewire\Admin\SupportTickets\Queue as AdminSupportTicketQueue;
use App\Livewire\Admin\SupportTickets\Show as AdminSupportTicketShow;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Checkout\BankTransferCheckout;
use App\Livewire\Checkout\Complete as CheckoutComplete;
use App\Livewire\Checkout\Show as CheckoutShow;
use App\Livewire\Checkout\TestCheckout;
use App\Livewire\CourseCatalogue;
use App\Livewire\Dashboard;
use App\Livewire\Discussions\Index as DiscussionsIndex;
use App\Livewire\Discussions\Thread as DiscussionThreadShow;
use App\Livewire\Lecturer\Announcements as LecturerAnnouncements;
use App\Livewire\Lecturer\Assignments as LecturerAssignments;
use App\Livewire\Lecturer\AssignmentSubmissions as LecturerAssignmentSubmissions;
use App\Livewire\Lecturer\Attendance as LecturerAttendance;
use App\Livewire\Lecturer\CompletionCriteria;
use App\Livewire\Lecturer\CourseForm;
use App\Livewire\Lecturer\CurriculumBuilder;
use App\Livewire\Lecturer\Earnings as LecturerEarnings;
use App\Livewire\Lecturer\GradeAttempt;
use App\Livewire\Lecturer\LiveClasses as LecturerLiveClasses;
use App\Livewire\Lecturer\MyCourses;
use App\Livewire\Lecturer\QuizAttempts as LecturerQuizAttempts;
use App\Livewire\Lecturer\QuizQuestions;
use App\Livewire\Lecturer\Quizzes as LecturerQuizzes;
use App\Livewire\LecturerApplicationForm;
use App\Livewire\LecturerDirectory;
use App\Livewire\Student\Assessments as StudentAssessments;
use App\Livewire\Student\Certificates as StudentCertificates;
use App\Livewire\Student\Classroom;
use App\Livewire\Student\MyCourses as StudentMyCourses;
use App\Livewire\Student\Schedule as StudentSchedule;
use App\Livewire\Student\SubmitAssignment;
use App\Livewire\Student\Subscriptions as StudentSubscriptions;
use App\Livewire\Student\TakeQuiz;
use App\Livewire\SupportTickets\Index as SupportTicketsIndex;
use App\Livewire\SupportTickets\Show as SupportTicketShow;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('about', [PublicPageController::class, 'about'])->name('about');
Route::get('how-it-works', [PublicPageController::class, 'howItWorks'])->name('how-it-works');
Route::get('assessments', [PublicPageController::class, 'assessments'])->name('assessments');
Route::get('faq', [PublicPageController::class, 'faq'])->name('faq');
Route::get('contact', [PublicPageController::class, 'contact'])->name('contact');
Route::get('terms', [PublicPageController::class, 'terms'])->name('terms');
Route::get('privacy', [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('refund-policy', [PublicPageController::class, 'refundPolicy'])->name('refund-policy');
Route::get('cookie-policy', [PublicPageController::class, 'cookiePolicy'])->name('cookie-policy');

Route::get('courses', CourseCatalogue::class)->name('courses.index');
Route::get('courses/{slug}', [PublicCourseController::class, 'show'])->name('courses.show');
Route::get('lecturers', LecturerDirectory::class)->name('lecturers.index');
Route::get('lecturers/{user}', [PublicCourseController::class, 'lecturerProfile'])->name('lecturers.show');

Route::get('locale/{locale}', LocaleController::class)->name('locale.update');

Route::get('certificates/verify/{certificateNumber?}', [CertificateVerificationController::class, 'show'])->name('certificates.verify');

Route::post('webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::post('webhooks/paypal', [PayPalWebhookController::class, 'handle'])->name('webhooks.paypal');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('lecturer-application', LecturerApplicationForm::class)
    ->middleware(['auth', 'verified'])
    ->name('lecturer-application');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('my-courses', StudentMyCourses::class)->name('my-courses.index');
    Route::get('my-courses/{course}/classroom', Classroom::class)->name('my-courses.classroom');
    Route::get('my-assessments', StudentAssessments::class)->name('my-assessments.index');
    Route::get('my-schedule', StudentSchedule::class)->name('my-schedule.index');
    Route::get('quizzes/{quiz}/take', TakeQuiz::class)->name('my-courses.quizzes.take');
    Route::get('assignments/{assignment}/submit', SubmitAssignment::class)->name('my-courses.assignments.submit');
    Route::get('assignments/{assignment}/attachment', [AssignmentAttachmentController::class, 'download'])->name('assignments.attachment.download');
    Route::get('videos/{recordedVideo}/stream', [VideoStreamController::class, 'show'])->middleware('protected.media')->name('video.stream');
    Route::get('lesson-resources/{lessonResource}/view', [LessonResourceController::class, 'view'])->middleware('protected.media')->name('lesson-resources.view');
    Route::get('assignment-submissions/{assignmentSubmission}/download', [AssignmentSubmissionController::class, 'download'])->name('assignment-submissions.download');
    Route::get('my-certificates', StudentCertificates::class)->name('my-certificates.index');
    Route::get('certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('courses/{course}/discussions', DiscussionsIndex::class)->name('courses.discussions.index');
    Route::get('discussions/{thread}', DiscussionThreadShow::class)->name('courses.discussions.show');

    Route::get('courses/{course}/subscribe', CheckoutShow::class)->name('checkout.show');
    Route::get('checkout/test/{subscription}', TestCheckout::class)->name('checkout.test.show');
    Route::get('checkout/bank-transfer/{subscription}', BankTransferCheckout::class)->name('checkout.bank-transfer.show');
    Route::get('checkout/{subscription}/complete', CheckoutComplete::class)->name('checkout.complete');
    Route::get('transactions/{transaction}/receipt', [TransactionReceiptController::class, 'download'])->name('transactions.receipt.download');

    Route::get('my-subscriptions', StudentSubscriptions::class)->name('my-subscriptions.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

    Route::get('support-tickets', SupportTicketsIndex::class)->name('support-tickets.index');
    Route::get('support-tickets/{supportTicket}', SupportTicketShow::class)->name('support-tickets.show');
    Route::get('support-tickets/{supportTicket}/attachment', [SupportTicketAttachmentController::class, 'download'])->name('support-tickets.attachment');
});

Route::middleware(['auth', 'verified', 'permission:manage own courses'])
    ->prefix('lecturer')
    ->name('lecturer.')
    ->group(function () {
        Route::get('courses', MyCourses::class)->name('courses.index');
        Route::get('courses/create', CourseForm::class)->name('courses.create');
        Route::get('courses/{course}/edit', CourseForm::class)->name('courses.edit');
        Route::get('courses/{course}/curriculum', CurriculumBuilder::class)->name('courses.curriculum');
        Route::get('courses/{course}/live-classes', LecturerLiveClasses::class)->name('courses.live-classes');
        Route::get('live-classes/{liveClass}/attendance', LecturerAttendance::class)->name('live-classes.attendance');
        Route::get('courses/{course}/quizzes', LecturerQuizzes::class)->name('courses.quizzes');
        Route::get('quizzes/{quiz}/questions', QuizQuestions::class)->name('quizzes.questions');
        Route::get('quizzes/{quiz}/grading', LecturerQuizAttempts::class)->name('quizzes.grading');
        Route::get('quiz-attempts/{attempt}/grade', GradeAttempt::class)->name('quiz-attempts.grade');
        Route::get('quiz-submissions/{quizAttemptAnswer}/download', [QuizSubmissionController::class, 'download'])->name('quiz-submissions.download');
        Route::get('courses/{course}/assignments', LecturerAssignments::class)->name('courses.assignments');
        Route::get('assignments/{assignment}/submissions', LecturerAssignmentSubmissions::class)->name('assignments.submissions');
        Route::get('courses/{course}/completion-criteria', CompletionCriteria::class)->name('courses.completion-criteria');
        Route::get('courses/{course}/announcements', LecturerAnnouncements::class)->name('courses.announcements');
        Route::get('earnings', LecturerEarnings::class)->name('earnings');
    });

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('settings', AdminSettings::class)
            ->middleware('permission:manage settings')
            ->name('settings');

        Route::get('users', AdminUsers::class)
            ->middleware('permission:manage users')
            ->name('users.index');

        Route::get('categories', AdminCategories::class)
            ->middleware('permission:manage categories')
            ->name('categories');

        Route::get('lecture-rooms', AdminLectureRooms::class)
            ->middleware('permission:manage categories')
            ->name('lecture-rooms');

        Route::middleware('permission:manage lecturer applications')->group(function () {
            Route::get('lecturer-applications', LecturerApplications::class)->name('lecturer-applications.index');
            Route::get('lecturer-applications/{lecturerProfile}', LecturerApplicationReview::class)->name('lecturer-applications.show');
            Route::get('lecturer-documents/{lecturerDocument}/download', [LecturerDocumentController::class, 'download'])->name('lecturer-documents.download');
        });

        Route::middleware('permission:review courses')->group(function () {
            Route::get('courses', CourseReviewQueue::class)->name('courses.index');
            Route::get('courses/{course}', CourseReview::class)->name('courses.show');
        });

        Route::get('enrolments', AdminEnrolments::class)
            ->middleware('permission:manage enrolments')
            ->name('enrolments');

        Route::get('certificates', AdminCertificates::class)
            ->middleware('permission:manage certificates')
            ->name('certificates');

        Route::middleware('permission:manage finances')
            ->prefix('finance')
            ->name('finance.')
            ->group(function () {
                Route::get('transactions', AdminFinanceTransactions::class)->name('transactions');
                Route::get('invoices', AdminFinanceInvoices::class)->name('invoices');
                Route::get('revenue', AdminFinanceRevenueReport::class)->name('revenue');
                Route::get('coupons', AdminFinanceCoupons::class)->name('coupons');
                Route::get('bank-accounts', AdminFinanceBankAccounts::class)->name('bank-accounts');
            });

        Route::middleware('permission:manage lecturer commissions')->group(function () {
            Route::get('finance/commissions', AdminFinanceCommissions::class)->name('finance.commissions');
            Route::get('finance/payouts', AdminFinancePayouts::class)->name('finance.payouts');
        });

        Route::get('audit-logs', AdminAuditLogs::class)
            ->middleware('permission:view audit logs')
            ->name('audit-logs');

        Route::middleware('permission:manage support tickets')->group(function () {
            Route::get('support-tickets', AdminSupportTicketQueue::class)->name('support-tickets.index');
            Route::get('support-tickets/{supportTicket}', AdminSupportTicketShow::class)->name('support-tickets.show');
        });

        Route::get('reports', AdminReports::class)
            ->middleware('permission:view reports')
            ->name('reports');

        Route::middleware('permission:manage content')->group(function () {
            Route::get('pages', AdminPages::class)->name('pages');
            Route::get('faqs', AdminFaqs::class)->name('faqs');
            Route::get('news', AdminNews::class)->name('news');
        });
    });

require __DIR__.'/auth.php';
