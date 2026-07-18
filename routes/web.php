<?php

use App\Http\Controllers\Admin\LecturerDocumentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LessonResourceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PublicCourseController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\VideoStreamController;
use App\Livewire\Admin\Categories as AdminCategories;
use App\Livewire\Admin\CourseReview;
use App\Livewire\Admin\CourseReviewQueue;
use App\Livewire\Admin\Enrolments as AdminEnrolments;
use App\Livewire\Admin\LecturerApplicationReview;
use App\Livewire\Admin\LecturerApplications;
use App\Livewire\Admin\Settings as AdminSettings;
use App\Livewire\Checkout\Complete as CheckoutComplete;
use App\Livewire\Checkout\Show as CheckoutShow;
use App\Livewire\Checkout\TestCheckout;
use App\Livewire\CourseCatalogue;
use App\Livewire\LecturerApplicationForm;
use App\Livewire\Lecturer\Attendance as LecturerAttendance;
use App\Livewire\Lecturer\CourseForm;
use App\Livewire\Lecturer\CurriculumBuilder;
use App\Livewire\Lecturer\LiveClasses as LecturerLiveClasses;
use App\Livewire\Lecturer\MyCourses;
use App\Livewire\LecturerDirectory;
use App\Livewire\Student\Classroom;
use App\Livewire\Student\MyCourses as StudentMyCourses;
use App\Livewire\Student\Subscriptions as StudentSubscriptions;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('about', [PublicPageController::class, 'about'])->name('about');
Route::get('how-it-works', [PublicPageController::class, 'howItWorks'])->name('how-it-works');
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

Route::view('dashboard', 'dashboard')
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
    Route::get('videos/{recordedVideo}/stream', [VideoStreamController::class, 'show'])->name('video.stream');
    Route::get('lesson-resources/{lessonResource}/download', [LessonResourceController::class, 'download'])->name('lesson-resources.download');

    Route::get('courses/{course}/subscribe', CheckoutShow::class)->name('checkout.show');
    Route::get('checkout/test/{subscription}', TestCheckout::class)->name('checkout.test.show');
    Route::get('checkout/{subscription}/complete', CheckoutComplete::class)->name('checkout.complete');

    Route::get('my-subscriptions', StudentSubscriptions::class)->name('my-subscriptions.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
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
    });

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('settings', AdminSettings::class)
            ->middleware('permission:manage settings')
            ->name('settings');

        Route::get('categories', AdminCategories::class)
            ->middleware('permission:manage categories')
            ->name('categories');

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
    });

require __DIR__.'/auth.php';
