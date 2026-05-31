<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * تسجيل عمليات النظام في audit_logs (لا يرمي استثناءات للمستخدم؛ يُبلّغ فقط عند فشل الكتابة).
 */
final class AuditLogger
{
    /** Security-sensitive audit channel (role, billing, auth, patient clinical). */
    public static function security(
        string $action,
        string $module,
        ?int $recordId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $modelType = null,
    ): void {
        self::log($action, $module, $recordId, $description, $oldValues, $newValues, $modelType);
    }

    public static function log(
        string $action,
        string $module,
        ?int $recordId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $modelType = null,
    ): void {
        try {
            $ua = request()->userAgent();
            if ($ua !== null && strlen($ua) > 2000) {
                $ua = substr($ua, 0, 2000);
            }

            AuditLog::query()->create([
                'user_id' => Auth::id(),
                'action' => $action,
                'module' => $module,
                'model_type' => $modelType,
                'record_id' => $recordId,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => $ua,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
