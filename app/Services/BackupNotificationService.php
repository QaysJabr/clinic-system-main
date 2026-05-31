<?php

namespace App\Services;

use App\Mail\DatabaseBackupResultMail;
use App\Models\AppNotification;
use App\Models\User;
use App\Support\AppNotificationType;
use Illuminate\Support\Facades\Mail;

final class BackupNotificationService
{
    public function notifySuccess(string $filename, int $prunedCount): void
    {
        if (! config('backup.notify_enabled', true)) {
            return;
        }

        if (! config('backup.notify_on_success', true)) {
            return;
        }

        $message = __('backups.notify_success_message', [
            'file' => $filename,
            'pruned' => $prunedCount,
            'time' => now()->timezone(config('app.timezone'))->format('d/m/Y H:i'),
        ]);

        $this->dispatch(true, $message, $filename);
    }

    public function notifyFailure(string $error): void
    {
        if (! config('backup.notify_enabled', true)) {
            return;
        }

        $message = __('backups.notify_failure_message', [
            'error' => $error,
            'time' => now()->timezone(config('app.timezone'))->format('d/m/Y H:i'),
        ]);

        $this->dispatch(false, $message, null);
    }

    private function dispatch(bool $success, string $message, ?string $filename): void
    {
        $this->notifyInApp($success, $message);
        $this->sendEmails($success, $message, $filename);
    }

    private function notifyInApp(bool $success, string $message): void
    {
        if (! config('backup.notify_in_app', true)) {
            return;
        }

        $recipients = app(InAppNotificationService::class)->recipientUserIdsForPlatformSuperAdmins();
        if ($recipients->isEmpty()) {
            return;
        }

        $title = $success
            ? __('backups.notify_success_title')
            : __('backups.notify_failure_title');

        $now = now();
        $rows = $recipients->map(fn (int $uid) => [
            'user_id' => $uid,
            'clinic_id' => null,
            'type' => AppNotificationType::PLATFORM_DATABASE_BACKUP,
            'title' => $title,
            'message' => $message,
            'related_type' => null,
            'related_id' => null,
            'is_read' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        AppNotification::query()->insert($rows);
    }

    private function sendEmails(bool $success, string $message, ?string $filename): void
    {
        if (! config('backup.notify_email', true)) {
            return;
        }

        $emails = $this->recipientEmails();
        if ($emails === []) {
            return;
        }

        $whatsappUrl = $this->whatsappAlertUrl($message);

        foreach ($emails as $email) {
            Mail::to($email)->send(new DatabaseBackupResultMail(
                success: $success,
                bodyMessage: $message,
                filename: $filename,
                whatsappUrl: $whatsappUrl,
            ));
        }
    }

    /**
     * @return list<string>
     */
    private function recipientEmails(): array
    {
        $configured = (string) config('backup.notify_emails', '');
        $fromEnv = array_filter(array_map('trim', explode(',', $configured)));

        if ($fromEnv !== []) {
            return array_values(array_unique($fromEnv));
        }

        $support = trim((string) config('saas.support.email', ''));
        if ($support !== '') {
            return [$support];
        }

        return User::query()
            ->withoutGlobalScopes()
            ->whereNull('clinic_id')
            ->whereHas('roles', fn ($r) => $r->where('name', 'super_admin'))
            ->whereNotNull('email')
            ->pluck('email')
            ->filter(fn ($e) => is_string($e) && filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    private function whatsappAlertUrl(string $message): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) config('backup.notify_whatsapp', config('saas.support.whatsapp', '')));
        if ($phone === '') {
            return null;
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }
}
