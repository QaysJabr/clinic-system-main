<?php

namespace App\Mail;

use App\Models\AppointmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly AppointmentReminder $reminder,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.appointment_reminder_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.appointment-reminder',
        );
    }
}
