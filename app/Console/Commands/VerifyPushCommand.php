<?php

namespace App\Console\Commands;

use App\Services\Push\FcmAccessTokenProvider;
use App\Services\Push\FcmPushService;
use Illuminate\Console\Command;

class VerifyPushCommand extends Command
{
    protected $signature = 'push:verify
                            {--token= : إرسال إشعار تجريبي لتوكن FCM (اختياري)}';

    protected $description = 'التحقق من إعداد Firebase Push (credentials + OAuth)';

    public function handle(FcmAccessTokenProvider $tokens, FcmPushService $fcm): int
    {
        $enabled = (bool) config('push.enabled', false);
        $path = (string) config('push.firebase.credentials');
        $projectId = (string) config('push.firebase.project_id');

        $this->line('PUSH_ENABLED: '.($enabled ? 'true' : 'false'));

        if (! $enabled) {
            $this->warn('Push معطّل — عيّن PUSH_ENABLED=true في .env');
        }

        if ($path === '' || ! is_readable($path)) {
            $this->error("ملف Firebase غير موجود: {$path}");
            $this->line('حمّله من Firebase Console → Service accounts → Generate new private key');
            $this->line('احفظه في: storage/app/firebase-credentials.json');

            return self::FAILURE;
        }

        $this->info("Credentials: {$path}");

        $json = json_decode((string) file_get_contents($path), true);
        if (! is_array($json)) {
            $this->error('ملف Firebase JSON غير صالح');

            return self::FAILURE;
        }

        $fromFile = (string) ($json['project_id'] ?? '');
        if ($projectId === '' && $fromFile !== '') {
            $this->line("FIREBASE_PROJECT_ID: (فارغ — سيُستخدم من الملف: {$fromFile})");
        } elseif ($projectId !== '') {
            $this->line("FIREBASE_PROJECT_ID: {$projectId}");
            if ($fromFile !== '' && $projectId !== $fromFile) {
                $this->warn("تحذير: project_id في .env ({$projectId}) ≠ الملف ({$fromFile})");
            }
        } else {
            $this->error('project_id غير موجود في .env ولا في ملف credentials');

            return self::FAILURE;
        }

        try {
            $tokens->get();
            $this->info('OAuth token: OK — Laravel يقدر يتصل بـ FCM');
        } catch (\Throwable $e) {
            $this->error('OAuth فشل: '.$e->getMessage());

            return self::FAILURE;
        }

        $testToken = $this->option('token');
        if (is_string($testToken) && $testToken !== '') {
            $result = $fcm->sendToToken(
                $testToken,
                'Clinic System',
                'إشعار تجريبي — Push يعمل ✅',
                ['type' => 'test'],
            );

            match ($result) {
                FcmPushService::RESULT_SENT => $this->info('تم إرسال الإشعار التجريبي بنجاح'),
                FcmPushService::RESULT_INVALID_TOKEN => $this->error('التوكن غير صالح (UNREGISTERED)'),
                FcmPushService::RESULT_SKIPPED => $this->warn('تم تخطي الإرسال — PUSH_ENABLED=false'),
                default => $this->error("فشل الإرسال: {$result}"),
            };
        }

        $this->newLine();
        $this->info('Firebase Push جاهز على Laravel ✅');
        $this->line('تأكد أيضاً من: mobile/google-services.json + queue worker شغّال');

        return self::SUCCESS;
    }
}
