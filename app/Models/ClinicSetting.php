<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClinicSetting extends Model
{
    use BelongsToClinic;

    protected $table = 'clinic_settings';

    protected $fillable = [
        'clinic_id',
        'clinic_name',
        'clinic_logo',
        'clinic_phone',
        'clinic_email',
        'clinic_address',
        'currency',
        'opening_cash_balance',
        'invoice_notes',
        'report_footer',
        'require_invoice_for_visit',
        'enforce_one_invoice_per_visit',
        'scheduling_slot_minutes',
        'scheduling_day_start',
        'scheduling_day_end',
        'scheduling_buffer_minutes',
        'scheduling_allow_overbooking',
        'scheduling_reminders_enabled',
        'require_two_factor',
    ];

    protected function casts(): array
    {
        return [
            'require_invoice_for_visit' => 'boolean',
            'enforce_one_invoice_per_visit' => 'boolean',
            'require_two_factor' => 'boolean',
            'scheduling_allow_overbooking' => 'boolean',
            'scheduling_reminders_enabled' => 'boolean',
            'opening_cash_balance' => 'decimal:2',
        ];
    }

    /**
     * إعدادات العيادة الحالية للمستخدم المعرّف (أو العيادة الافتراضية).
     */
    public static function current(): self
    {
        $clinicId = static::resolveCurrentClinicId();

        $row = static::query()->where('clinic_id', $clinicId)->first();

        if ($row !== null) {
            return $row;
        }

        return static::query()->create([
            'clinic_id' => $clinicId,
            'clinic_name' => config('app.name', 'العيادة'),
        ]);
    }

    private static function resolveCurrentClinicId(): int
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return (int) config('tenancy.default_clinic_id');
        }

        if ($user && $user->clinic_id) {
            return (int) $user->clinic_id;
        }

        return (int) config('tenancy.default_clinic_id');
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * رابط عرض الشعار للمتصفح.
     *
     * الملفات تُخزَّن على قرص public؛ بدون symlink لـ public/storage يفشل asset('/storage/...').
     * لذلك نستخدم مسارًا داخل التطبيق يقرأ الملف من التخزين مباشرةً.
     */
    public function logoPublicUrl(): ?string
    {
        $relative = $this->normalizedLogoRelativePath();

        if ($relative === null || ! Storage::disk('public')->exists($relative)) {
            return null;
        }

        return route('settings.logo', ['setting' => $this->id], false);
    }

    /**
     * مسار الشعار النسبي على قرص public (بدون .. وبدون بادئة storage/ المكررة).
     */
    public function normalizedLogoRelativePath(): ?string
    {
        if ($this->clinic_logo === null || $this->clinic_logo === '') {
            return null;
        }

        $relative = ltrim(str_replace('\\', '/', (string) $this->clinic_logo), '/');

        if (str_contains($relative, '..')) {
            return null;
        }

        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        return $relative !== '' ? $relative : null;
    }
}
