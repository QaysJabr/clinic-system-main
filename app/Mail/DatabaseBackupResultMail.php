<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class DatabaseBackupResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly bool $success,
        public readonly string $bodyMessage,
        public readonly ?string $filename,
        public readonly ?string $whatsappUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->success
                ? __('backups.mail_success_subject', ['app' => config('app.name')])
                : __('backups.mail_failure_subject', ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.database-backup-result',
            with: [
                'success' => $this->success,
                'bodyMessage' => $this->bodyMessage,
                'filename' => $this->filename,
                'whatsappUrl' => $this->whatsappUrl,
                'appUrl' => config('app.url'),
            ],
        );
    }
}
