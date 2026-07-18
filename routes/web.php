<?php

use App\Http\Controllers\LocaleController;
use App\Livewire\Admin\Settings as AdminSettings;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

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
