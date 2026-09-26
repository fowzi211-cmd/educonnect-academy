<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Course;
use App\Models\LecturerCommissionRate;
use App\Models\PayoutRequest;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\Finance\PayoutService;
use App\Services\Payments\PaymentService;
use App\Services\Support\SupportTicketService;
use Illuminate\Database\Seeder;

/**
 * Demo data for the Phase 6 admin features: a commission rate override, a
 * handful of real subscriptions run through PaymentService so commission
 * earnings are calculated the same way a live payment would, payout
 * requests parked in each stage of their lifecycle, and support tickets
 * spanning categories/priorities/statuses. Guards each block with an
 * existence check so it's safe to re-run.
 */
class Phase6DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCommissionOverride();
        $this->seedEarningsAndPayouts();
        $this->seedSupportTickets();
        $this->seedBankAccount();
    }

    protected function seedBankAccount(): void
    {
        BankAccount::firstOrCreate(
            ['account_number' => '1234567890123 (DEMO)'],
            [
                'bank_name' => 'Demo National Bank',
                'account_name' => 'Dr. Nada Center (Demo)',
                'iban' => 'SA0000000000000000000000',
                'swift_code' => 'DEMOSDKH',
                'currency' => 'SDG',
                'is_active' => true,
                'notes' => 'This is a demo account for local development only — not a real bank account.',
            ]
        );
    }

    protected function seedCommissionOverride(): void
    {
        $lecturer = User::where('email', 'lecturer1@educonnect.test')->first();
        $course = Course::where('title', 'Introduction to Statistics')->first();

        if (! $lecturer || ! $course) {
            return;
        }

        LecturerCommissionRate::firstOrCreate(
            ['user_id' => $lecturer->id, 'course_id' => $course->id],
            ['type' => LecturerCommissionRate::TYPE_PERCENTAGE, 'value' => 75]
        );
    }

    protected function seedEarningsAndPayouts(): void
    {
        $payments = app(PaymentService::class);
        $payouts = app(PayoutService::class);

        $stats = Course::where('title', 'Introduction to Statistics')->first();
        $python = Course::where('title', 'Python for Beginners')->first();
        $lecturer1 = User::where('email', 'lecturer1@educonnect.test')->first();
        $lecturer2 = User::where('email', 'lecturer2@educonnect.test')->first();

        if (! $stats || ! $python || ! $lecturer1 || ! $lecturer2) {
            return;
        }

        // Temporarily waive the payout threshold so these demo requests go
        // through regardless of what an admin has configured it to; the
        // real setting is restored once the demo payouts are in place.
        $originalThreshold = Setting::get('commission.payout_threshold', 100);
        Setting::set('commission.payout_threshold', 0, 'commission');

        // Enough unpaid earnings to clear the default payout threshold, so
        // the demo payout requests below succeed the same way a real one would.
        $this->subscribeIfNeeded($payments, 'student7@educonnect.test', $stats);
        $this->subscribeIfNeeded($payments, 'student8@educonnect.test', $stats);
        $this->subscribeIfNeeded($payments, 'student9@educonnect.test', $stats);

        // Lecturer 1's first payout: taken all the way through to paid.
        if (! PayoutRequest::where('user_id', $lecturer1->id)->exists()) {
            $request = $payouts->requestPayout($lecturer1);
            $payouts->approve($request, User::where('email', 'finance@educonnect.test')->first());
            $payouts->markPaid($request, User::where('email', 'finance@educonnect.test')->first(), 'DEMO-PAYOUT-2026-001');

            // A later subscription generates a fresh unpaid earning so the
            // lecturer's statement isn't empty after being paid out once.
            $this->subscribeIfNeeded($payments, 'student2@educonnect.test', $stats);

            $second = $payouts->requestPayout($lecturer1);
            $payouts->approve($second, User::where('email', 'finance@educonnect.test')->first());
        }

        $this->subscribeIfNeeded($payments, 'student10@educonnect.test', $python);
        $this->subscribeIfNeeded($payments, 'student1@educonnect.test', $python);

        // Lecturer 2's payout is left pending finance review.
        if (! PayoutRequest::where('user_id', $lecturer2->id)->exists()) {
            $payouts->requestPayout($lecturer2);
        }

        Setting::set('commission.payout_threshold', $originalThreshold, 'commission');
    }

    protected function subscribeIfNeeded(PaymentService $payments, string $studentEmail, Course $course): void
    {
        $student = User::where('email', $studentEmail)->first();

        if (! $student) {
            return;
        }

        $alreadySubscribed = $course->subscriptions()
            ->where('user_id', $student->id)
            ->whereIn('status', Subscription::ACCESS_GRANTING_STATUSES)
            ->exists();

        if ($alreadySubscribed) {
            return;
        }

        $result = $payments->initiateSubscription($student, $course);
        $payments->confirmPayment($result['transaction']->fresh(), true, 'demo_gw_'.$student->id.'_'.$course->id);
    }

    protected function seedSupportTickets(): void
    {
        $tickets = app(SupportTicketService::class);
        $support = User::where('email', 'support@educonnect.test')->first();

        if (! $support || SupportTicket::exists()) {
            return;
        }

        $student1 = User::where('email', 'student1@educonnect.test')->first();
        $student2 = User::where('email', 'student2@educonnect.test')->first();
        $student3 = User::where('email', 'student3@educonnect.test')->first();
        $student4 = User::where('email', 'student4@educonnect.test')->first();
        $lecturer1 = User::where('email', 'lecturer1@educonnect.test')->first();

        // 1. Freshly opened, untouched.
        if ($student1) {
            $tickets->create($student1, [
                'category' => SupportTicket::CATEGORY_PAYMENT,
                'priority' => 'high',
                'subject' => 'Charged twice for the same course',
                'description' => 'I was billed twice this month for Introduction to Statistics. Can you check my transaction history?',
            ]);
        }

        // 2. Assigned, staff replied, waiting on the student.
        if ($student2) {
            $ticket = $tickets->create($student2, [
                'category' => SupportTicket::CATEGORY_TECHNICAL,
                'priority' => 'medium',
                'subject' => 'Video player keeps buffering',
                'description' => 'The recorded lessons keep buffering every few seconds even on a fast connection.',
            ]);
            $tickets->assign($ticket, $support, $support);
            $tickets->reply($ticket, $support, 'Thanks for the report — could you tell us which browser and device you were using?');
        }

        // 3. Resolved, with an internal note staff left for each other.
        if ($lecturer1) {
            $ticket = $tickets->create($lecturer1, [
                'category' => SupportTicket::CATEGORY_ACCOUNT,
                'priority' => 'low',
                'subject' => 'Need my payout bank details updated',
                'description' => 'I changed banks and need my payout details updated on file.',
            ]);
            $tickets->assign($ticket, $support, $support);
            $tickets->reply($ticket, $support, 'Verified identity via registered email, escalating to finance to update the record.', isInternalNote: true);
            $tickets->reply($ticket, $support, 'Your bank details have been updated. Let us know if the next payout looks correct.');
            $tickets->updateStatus($ticket, SupportTicket::STATUS_RESOLVED, $support);
        }

        // 4. Reopened after being resolved.
        if ($student3) {
            $ticket = $tickets->create($student3, [
                'category' => SupportTicket::CATEGORY_CERTIFICATE,
                'priority' => 'urgent',
                'subject' => 'Certificate has a typo in my name',
                'description' => 'My certificate for Introduction to Statistics spells my name incorrectly.',
            ]);
            $tickets->assign($ticket, $support, $support);
            $tickets->reply($ticket, $support, 'Apologies for that — we have reissued your certificate with the corrected spelling.');
            $tickets->updateStatus($ticket, SupportTicket::STATUS_RESOLVED, $support);
            $tickets->reopen($ticket, $student3);
            $tickets->reply($ticket, $student3, 'The new certificate still has the old spelling, please check again.');
        }

        // 5. Closed.
        if ($student4) {
            $ticket = $tickets->create($student4, [
                'category' => SupportTicket::CATEGORY_REFUND,
                'priority' => 'medium',
                'subject' => 'Requesting a refund for Python for Beginners',
                'description' => 'The course pace was too fast for me, I would like to request a refund within the trial window.',
            ]);
            $tickets->assign($ticket, $support, $support);
            $tickets->reply($ticket, $support, 'Your refund has been processed and should reflect on your original payment method within a few business days.');
            $tickets->updateStatus($ticket, SupportTicket::STATUS_CLOSED, $support);
        }
    }
}
