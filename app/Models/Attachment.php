<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'visit_id',
        'file_name',
        'file_path',
        'storage_disk',
        'file_type',
        'category',
        'file_size',
        'notes',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Attachment $attachment): void {
            $clinicId = null;
            if ($attachment->visit_id) {
                $visit = Visit::withoutGlobalScopes()->select(['id', 'clinic_id'])->find($attachment->visit_id);
                $clinicId = $visit?->clinic_id;
            }
            if (! $clinicId && $attachment->patient_id) {
                $patient = Patient::withoutGlobalScopes()->select(['id', 'clinic_id'])->find($attachment->patient_id);
                $clinicId = $patient?->clinic_id;
            }
            if ($clinicId) {
                $attachment->clinic_id = $clinicId;
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function storageDisk(): string
    {
        return $this->storage_disk ?: 'public';
    }

    public function deleteFileFromStorage(): void
    {
        if (! $this->file_path) {
            return;
        }

        $disk = $this->storageDisk();
        if (Storage::disk($disk)->exists($this->file_path)) {
            Storage::disk($disk)->delete($this->file_path);
        }
    }
}
