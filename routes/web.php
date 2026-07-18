<?php

use App\Http\Controllers\Admin\LecturerDocumentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PublicPageController;
use App\Livewire\Admin\Categories as AdminCategories;
use App\Livewire\Admin\LecturerApplicationReview;
use App\Livewire\Admin\LecturerApplications;
use App\Livewire\Admin\Settings as AdminSettings;
use App\Livewire\LecturerApplicationForm;
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
    });

require __DIR__.'/auth.php';
