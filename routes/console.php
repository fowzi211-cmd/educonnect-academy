<?php

use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (PaymentService $payments) {
    $payments->expireLapsedSubscriptions();
    $payments->expireLapsedGracePeriods();
})->daily()->name('subscriptions:process-lifecycle');
