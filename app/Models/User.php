<?php

namespace App\Models;

use Database\Factories\UserFactory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'avatar_path', 'clinic_id'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * رابط عرض الصورة الشخصية (مخزّنة أو افتراضية من الأحرف الأولى).
     */
    public function avatarUrl(): string
    {
        $relative = $this->normalizedAvatarRelativePath();

        if ($relative !== null && Storage::disk('public')->exists($relative)) {
            return route('profile.avatar', ['user' => $this->id], false);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0F4C81&color=fff&size=128';
    }

    /**
     * مسار الصورة النسبي على قرص public (بدون .. وبدون بادئة storage/ المكررة).
     */
    public function normalizedAvatarRelativePath(): ?string
    {
        if ($this->avatar_path === null || $this->avatar_path === '') {
            return null;
        }

        $relative = ltrim(str_replace('\\', '/', (string) $this->avatar_path), '/');

        if (str_contains($relative, '..')) {
            return null;
        }

        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        return $relative !== '' ? $relative : null;
    }

    /**
     * حذف ملف الصورة من التخزين إن وُجد.
     */
    public function deleteStoredAvatar(): void
    {
        $relative = $this->normalizedAvatarRelativePath();

        if ($relative !== null && Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }

    /**
     * نص تمييز المستخدم في القوائم والجداول بدون البريد (أدوار بالعربية + رقم المعرف).
     * يعتمد على تحميل علاقة roles عند الحاجة.
     */
    public function disambiguationLabel(): string
    {
        $roleLabels = [
            'admin' => 'مدير النظام',
            'receptionist' => 'موظف استقبال',
            'doctor' => 'طبيب',
            'accountant' => 'محاسب',
        ];

        $rolePart = '';
        if ($this->relationLoaded('roles') && $this->roles->isNotEmpty()) {
            $rolePart = $this->roles->pluck('name')
                ->map(fn (string $n) => $roleLabels[$n] ?? $n)
                ->unique()
                ->values()
                ->implode('، ');
        }

        return $rolePart !== ''
            ? $rolePart.' · #'.$this->id
            : 'معرّف #'.$this->id;
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isPlatformOwner(): bool
    {
        if (! $this->isSuperAdmin() || $this->clinic_id !== null) {
            return false;
        }

        $ownerEmail = config('platform.owner_email');

        if (is_string($ownerEmail) && $ownerEmail !== '' && strcasecmp($this->email, $ownerEmail) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * سجل موظف مرتبط بحساب المستخدم (اختياري).
     *
     * @return HasOne<Staff, $this>
     */
    public function staffRecord(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * سجل الطبيب المرتبط بحساب المستخدم (عبر موظف دوره طبيب).
     */
    public function linkedDoctor(): ?Doctor
    {
        $staff = $this->relationLoaded('staffRecord')
            ? $this->staffRecord
            : Staff::query()->withoutGlobalScopes()->where('user_id', $this->id)->first();

        if ($staff) {
            return Doctor::query()
                ->withoutGlobalScopes()
                ->where('staff_id', $staff->id)
                ->when($this->clinic_id !== null, fn ($q) => $q->where('clinic_id', $this->clinic_id))
                ->first();
        }

        return $this->resolveDoctorByIdentityFallback();
    }

    /**
     * عند غياب staff↔user: مطابقة بريد أو اسم واحد فقط داخل العيادة (إعداد شائع بعد إنشاء طبيب بدون موظف).
     */
    private function resolveDoctorByIdentityFallback(): ?Doctor
    {
        if ($this->clinic_id === null) {
            return null;
        }

        $base = Doctor::query()->withoutGlobalScopes()->where('clinic_id', $this->clinic_id);

        if (filled($this->email)) {
            $byEmail = (clone $base)
                ->whereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower(trim((string) $this->email))])
                ->get();
            if ($byEmail->count() === 1) {
                return $byEmail->first();
            }
        }

        if (filled($this->name)) {
            $byName = (clone $base)
                ->where('full_name', trim((string) $this->name))
                ->get();
            if ($byName->count() === 1) {
                return $byName->first();
            }
        }

        return null;
    }

    /**
     * نموذج التعويض الموسّع عبر سجل الموظف المرتبط.
     *
     * @return HasOneThrough<StaffCompensationProfile, Staff, $this>
     */
    public function staffCompensationProfile(): HasOneThrough
    {
        return $this->hasOneThrough(
            StaffCompensationProfile::class,
            Staff::class,
            'user_id',
            'staff_id',
            'id',
            'id',
        );
    }

    /**
     * @return HasMany<StaffPayment, $this>
     */
    public function createdStaffPayments(): HasMany
    {
        return $this->hasMany(StaffPayment::class, 'created_by');
    }
}
