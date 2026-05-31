<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PublicAppointmentBookingController as BookingController;
use App\Models\ClinicSetting;
use App\Support\AuditLogger;
use App\Support\Scheduling\SchedulingSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClinicSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $settings = ClinicSetting::current();

        $pageTitle = __('settings.clinic_page_title');

        if ($request->ajax()) {
            return view('settings.partials.content', compact('settings', 'pageTitle'));
        }

        return view('settings.edit', compact('settings', 'pageTitle'));
    }

    /**
     * عرض شعار العيادة من التخزين دون الاعتماد على symlink لـ public/storage.
     */
    public function logo(Request $request, ClinicSetting $setting): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user && $user->clinic_id !== null, 403);
        abort_unless((int) $user->clinic_id === (int) $setting->clinic_id, 403);

        $relative = $setting->normalizedLogoRelativePath();
        abort_unless($relative !== null && Storage::disk('public')->exists($relative), 404);

        return Storage::disk('public')->response($relative);
    }

    public function update(Request $request): RedirectResponse
    {
        if ($request->filled('scheduling_day_start')) {
            $request->merge([
                'scheduling_day_start' => SchedulingSettings::normalizeTime($request->input('scheduling_day_start')),
            ]);
        }

        if ($request->filled('scheduling_day_end')) {
            $request->merge([
                'scheduling_day_end' => SchedulingSettings::normalizeTime($request->input('scheduling_day_end')),
            ]);
        }

        $validated = $request->validate([
            'clinic_name' => ['required', 'string', 'max:255'],
            /* صور فقط — يمنع رفع ملفات تنفيذية بامتداد مضلل */
            'clinic_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
            'clinic_phone' => ['nullable', 'string', 'max:50'],
            'clinic_email' => ['nullable', 'string', 'email', 'max:255'],
            'clinic_address' => ['nullable', 'string', 'max:5000'],
            'currency' => ['nullable', 'string', 'max:50'],
            'opening_cash_balance' => ['nullable', 'numeric', 'min:-9999999999999.99', 'max:9999999999999.99'],
            'invoice_notes' => ['nullable', 'string', 'max:10000'],
            'report_footer' => ['nullable', 'string', 'max:10000'],
            'require_invoice_for_visit' => ['sometimes', 'boolean'],
            'enforce_one_invoice_per_visit' => ['sometimes', 'boolean'],
            'scheduling_slot_minutes' => ['nullable', 'integer', 'min:5', 'max:120'],
            'scheduling_day_start' => ['nullable', 'date_format:H:i'],
            'scheduling_day_end' => ['nullable', 'date_format:H:i'],
            'scheduling_buffer_minutes' => ['nullable', 'integer', 'min:0', 'max:60'],
            'scheduling_allow_overbooking' => ['sometimes', 'boolean'],
            'scheduling_reminders_enabled' => ['sometimes', 'boolean'],
        ]);

        $settings = ClinicSetting::current();

        $old = $this->settingsAuditSnapshot($settings);

        $oldLogoPath = $settings->clinic_logo;

        if ($request->hasFile('clinic_logo')) {
            $validated['clinic_logo'] = $request->file('clinic_logo')->store('clinic', 'public');
        } else {
            unset($validated['clinic_logo']);
        }

        $settings->fill([
            'clinic_name' => $validated['clinic_name'],
            'clinic_phone' => $validated['clinic_phone'] ?? null,
            'clinic_email' => $validated['clinic_email'] ?? null,
            'clinic_address' => $validated['clinic_address'] ?? null,
            'currency' => $validated['currency'] ?? null,
            'opening_cash_balance' => round((float) ($validated['opening_cash_balance'] ?? $settings->opening_cash_balance ?? 0), 2),
            'invoice_notes' => $validated['invoice_notes'] ?? null,
            'report_footer' => $validated['report_footer'] ?? null,
            'require_invoice_for_visit' => $request->boolean('require_invoice_for_visit'),
            'enforce_one_invoice_per_visit' => $request->boolean('enforce_one_invoice_per_visit'),
            'scheduling_slot_minutes' => $validated['scheduling_slot_minutes'] ?? $settings->scheduling_slot_minutes,
            'scheduling_day_start' => $validated['scheduling_day_start'] ?? $settings->scheduling_day_start,
            'scheduling_day_end' => $validated['scheduling_day_end'] ?? $settings->scheduling_day_end,
            'scheduling_buffer_minutes' => $validated['scheduling_buffer_minutes'] ?? $settings->scheduling_buffer_minutes,
            'scheduling_allow_overbooking' => $request->boolean('scheduling_allow_overbooking'),
            'scheduling_reminders_enabled' => $request->boolean('scheduling_reminders_enabled'),
        ]);

        if (array_key_exists('clinic_logo', $validated)) {
            $settings->clinic_logo = $validated['clinic_logo'];
        }

        $settings->save();

        if (array_key_exists('clinic_logo', $validated) && $oldLogoPath && $oldLogoPath !== $settings->clinic_logo) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        $settings->refresh();

        AuditLogger::log(
            'update',
            'settings',
            $settings->id,
            __('settings.audit_updated'),
            $old,
            $this->settingsAuditSnapshot($settings)
        );

        return redirect()
            ->route('settings.edit')
            ->with('success', __('settings.flash_saved'));
    }

    public function generatePublicBookingLink(Request $request): RedirectResponse
    {
        $settings = ClinicSetting::current();
        $clinicId = (int) $settings->clinic_id;

        $plain = BookingController::issueToken($clinicId, null, true);
        $url = route('booking.public.show', ['token' => $plain]);

        AuditLogger::log(
            'create',
            'settings',
            $settings->id,
            __('settings.audit_public_booking_link'),
            null,
            ['url' => $url]
        );

        return redirect()
            ->route('settings.edit')
            ->with('success', __('settings.flash_booking_link_created'))
            ->with('public_booking_url', $url);
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsAuditSnapshot(ClinicSetting $settings): array
    {
        return [
            'clinic_name' => $settings->clinic_name,
            'clinic_phone' => $settings->clinic_phone,
            'clinic_email' => $settings->clinic_email,
            'clinic_address' => $settings->clinic_address,
            'currency' => $settings->currency,
            'clinic_logo' => $settings->clinic_logo,
            'invoice_notes' => Str::limit((string) ($settings->invoice_notes ?? ''), 500),
            'report_footer' => Str::limit((string) ($settings->report_footer ?? ''), 500),
            'require_invoice_for_visit' => (bool) $settings->require_invoice_for_visit,
            'enforce_one_invoice_per_visit' => (bool) $settings->enforce_one_invoice_per_visit,
            'opening_cash_balance' => (float) ($settings->opening_cash_balance ?? 0),
            'scheduling_slot_minutes' => $settings->scheduling_slot_minutes,
            'scheduling_reminders_enabled' => (bool) $settings->scheduling_reminders_enabled,
        ];
    }
}
