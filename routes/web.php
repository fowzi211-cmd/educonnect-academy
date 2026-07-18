<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PublicPageController;
use App\Livewire\Admin\Settings as AdminSettings;
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

Route::middleware(['auth', 'verified', 'permission:manage settings'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('settings', AdminSettings::class)->name('settings');
    });

require __DIR__.'/auth.php';
