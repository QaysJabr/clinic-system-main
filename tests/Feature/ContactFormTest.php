<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    public function test_contact_form_sends_mail_to_support_address(): void
    {
        Mail::fake();

        config([
            'saas.support.email' => 'support@clinic.test',
        ]);

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('contact.store'), [
                'name' => 'Qays',
                'email' => 'visitor@example.com',
                'phone' => '0597360027',
                'subject' => 'Demo request',
                'message' => 'I want a trial account.',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHas('status');

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail): bool {
            return $mail->payload['email'] === 'visitor@example.com'
                && $mail->payload['subject'] === 'Demo request';
        });
    }
}
