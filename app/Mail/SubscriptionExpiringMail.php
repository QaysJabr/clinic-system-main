<?php

namespace App\Mail;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class SubscriptionExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Clinic $clinic,
        public readonly int $daysRemaining,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('saas.mail_expiring_subject', [
                'clinic' => $this->clinic->name,
                'days' => $this->daysRemaining,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.subscription-expiring',
            with: [
                'clinicName' => $this->clinic->name,
                'daysRemaining' => $this->daysRemaining,
                'expiresAt' => $this->clinic->subscription_expires_at?->format('d/m/Y H:i'),
                'billingUrl' => route('saas.billing'),
                'pricingUrl' => route('saas.pricing'),
            ],
        );
    }
}
