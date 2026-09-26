<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Suspends (never deletes) every seeded @educonnect.test demo account before
 * a real launch. Suspension is the same reversible mechanism Admin > Users
 * uses — it blocks login and ends any live session immediately, but every
 * account, its role, and its history (transactions, courses, enrolments)
 * stays intact and can be reactivated from Admin > Users if ever needed.
 *
 * Deliberately not run automatically by anything — the seeded accounts are
 * genuinely useful for testing right up until you're actually ready to go
 * live, so running this is a decision only you should make, at whatever
 * point you're done needing them.
 */
#[Signature('app:cleanup-demo-data {--force : Skip the confirmation prompt}')]
#[Description('Suspend all seeded @educonnect.test demo accounts ahead of a real launch')]
class CleanupDemoDataCommand extends Command
{
    public function handle(): int
    {
        $demoUsers = User::where('email', 'like', '%@educonnect.test')
            ->whereNull('suspended_at')
            ->get();

        if ($demoUsers->isEmpty()) {
            $this->info('No active @educonnect.test demo accounts found — nothing to do.');

            return self::SUCCESS;
        }

        $this->table(
            ['Email', 'Role'],
            $demoUsers->map(fn (User $user) => [$user->email, $user->getRoleNames()->implode(', ')]),
        );

        if (! $this->option('force') && ! $this->confirm(
            'Suspend all '.$demoUsers->count().' account(s) above? They will be logged out immediately and unable to sign in until reactivated from Admin > Users.'
        )) {
            $this->comment('Cancelled — nothing changed.');

            return self::SUCCESS;
        }

        foreach ($demoUsers as $user) {
            $user->forceFill(['suspended_at' => now()])->save();
            AuditLog::record('user.suspended', subject: $user, reason: 'Demo account cleanup ahead of launch');
        }

        $this->info("Suspended {$demoUsers->count()} demo account(s). Reactivate any of them anytime from Admin > Users.");

        return self::SUCCESS;
    }
}
