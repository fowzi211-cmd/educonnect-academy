<?php

namespace App\Livewire;

use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';

    public bool $sent = false;

    public function send(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $recipient = \App\Models\Setting::get('branding.contact_email');

        if ($recipient) {
            Mail::to($recipient)->send(new ContactMessageMail(
                senderName: $validated['name'],
                senderEmail: $validated['email'],
                subjectLine: $validated['subject'],
                body: $validated['message'],
            ));
        }

        $this->reset(['name', 'email', 'subject', 'message']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
