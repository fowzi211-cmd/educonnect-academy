<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\Enrolment;
use App\Models\LiveClass;
use App\Models\PayoutRequest;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $isStaff = $user->can('access admin panel');

        return view('livewire.dashboard', [
            'isStaff' => $isStaff,
            'operational' => $isStaff ? $this->operationalStats() : null,
            'financial' => $isStaff && $user->can('manage finances') ? $this->financialStats() : null,
            'support' => $isStaff && $user->can('manage support tickets') ? $this->supportStats() : null,
        ]);
    }

    protected function operationalStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_students' => User::role('student')->count(),
            'active_lecturers' => User::role('lecturer')->count(),
            'published_courses' => Course::where('status', Course::STATUS_PUBLISHED)->count(),
            'courses_awaiting_review' => Course::where('status', Course::STATUS_UNDER_REVIEW)->count(),
            'upcoming_live_classes' => LiveClass::where('status', LiveClass::STATUS_SCHEDULED)->where('starts_at', '>=', now())->count(),
            'completion_rate' => $this->overallCompletionRate(),
        ];
    }

    protected function overallCompletionRate(): float
    {
        $enrolled = Enrolment::where('status', Enrolment::STATUS_ACTIVE)->count();

        if ($enrolled === 0) {
            return 0.0;
        }

        return round((CourseCompletion::count() / $enrolled) * 100, 1);
    }

    protected function financialStats(): array
    {
        return [
            'active_subscriptions' => Subscription::whereIn('status', Subscription::ACCESS_GRANTING_STATUSES)->count(),
            'mrr' => Subscription::whereIn('status', Subscription::ACCESS_GRANTING_STATUSES)->sum('monthly_price'),
            'total_revenue' => Transaction::where('status', Transaction::STATUS_SUCCEEDED)->sum('amount'),
            'failed_payments' => Transaction::where('status', Transaction::STATUS_FAILED)->where('created_at', '>=', now()->subDays(30))->count(),
            'refunds_30d' => Refund::where('processed_at', '>=', now()->subDays(30))->sum('amount'),
            'outstanding_payouts' => PayoutRequest::whereIn('status', [PayoutRequest::STATUS_REQUESTED, PayoutRequest::STATUS_APPROVED])->sum('amount'),
        ];
    }

    protected function supportStats(): array
    {
        return [
            'open_tickets' => SupportTicket::whereIn('status', SupportTicket::OPEN_STATUSES)->count(),
            'unassigned_tickets' => SupportTicket::whereIn('status', SupportTicket::OPEN_STATUSES)->whereNull('assigned_to')->count(),
        ];
    }
}
