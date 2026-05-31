<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Mail\ContactMessageMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact', [
            'supportEmail' => (string) config('saas.support.email', ''),
            'supportWhatsapp' => (string) config('saas.support.whatsapp', ''),
            'supportPhone' => (string) config('saas.support.phone', ''),
        ]);
    }

    public function store(ContactFormRequest $request): RedirectResponse
    {
        $payload = [
            'name' => $request->string('name')->trim()->toString(),
            'email' => $request->string('email')->trim()->toString(),
            'phone' => $request->filled('phone') ? $request->string('phone')->trim()->toString() : null,
            'subject' => $request->string('subject')->trim()->toString(),
            'message' => $request->string('message')->trim()->toString(),
        ];

        $recipient = $this->resolveRecipient();

        if ($recipient === null) {
            Log::warning('contact.form_no_recipient', ['from' => $payload['email']]);

            return back()
                ->withInput()
                ->with('error', __('contact.send_failed_no_recipient'));
        }

        Mail::to($recipient)->send(new ContactMessageMail($payload));

        return redirect()
            ->route('contact')
            ->with('status', __('contact.sent_success'));
    }

    private function resolveRecipient(): ?string
    {
        $support = trim((string) config('saas.support.email', ''));
        if ($support !== '' && filter_var($support, FILTER_VALIDATE_EMAIL)) {
            return $support;
        }

        $owner = trim((string) config('platform.owner_email', ''));
        if ($owner !== '' && filter_var($owner, FILTER_VALIDATE_EMAIL)) {
            return $owner;
        }

        $from = trim((string) config('mail.from.address', ''));
        if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return $from;
        }

        return null;
    }
}
