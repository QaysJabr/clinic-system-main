<?php

namespace App\Mail;

use App\Models\Clinic;
use App\Models\SubscriptionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ManualSubscriptionPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Clinic $clinic,
        public readonly SubscriptionPayment $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('platform.mail_payment_recorded_subject', ['clinic' => $this->clinic->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.manual-subscription-payment',
            with: [
                'clinicName' => $this->clinic->name,
                'amount' => number_format((float) $this->payment->amount, 2),
                'paidAt' => $this->payment->paid_at?->format('d/m/Y H:i'),
                'expiresAt' => $this->clinic->subscription_expires_at?->format('d/m/Y H:i'),
                'notes' => $this->payment->notes,
                'billingUrl' => route('saas.billing'),
            ],
        );
    }
}
