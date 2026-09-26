<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Sends one plain-text email through whatever MAIL_MAILER is currently
 * configured, so an admin can confirm real SMTP credentials actually work
 * before relying on them for password resets and approval notifications.
 * Safe to run against the "log" driver too — it just writes to
 * storage/logs/laravel.log instead of sending anything.
 */
#[Signature('app:test-email {email : Address to send the test message to}')]
#[Description('Send a test email to verify the configured mail driver actually delivers')]
class TestEmailCommand extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("'{$email}' is not a valid email address.");

            return self::FAILURE;
        }

        $mailer = config('mail.default');
        $this->info("Sending via the '{$mailer}' mailer...");

        try {
            Mail::raw(
                'This is a test message from '.config('app.name').".\n\n".
                "If you're reading this in your inbox, outgoing email is configured correctly.\n".
                'Sent at: '.now()->toDateTimeString(),
                fn ($message) => $message->to($email)->subject('Test email — '.config('app.name')),
            );
        } catch (\Throwable $e) {
            $this->error('Sending failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($mailer === 'log') {
            $this->warn("MAIL_MAILER is still 'log' — nothing was actually sent. Check storage/logs/laravel.log for the rendered message, or switch MAIL_MAILER to 'smtp' with real credentials first.");

            return self::SUCCESS;
        }

        $this->info("Sent to {$email}. Check that inbox (and spam folder) to confirm delivery.");

        return self::SUCCESS;
    }
}
