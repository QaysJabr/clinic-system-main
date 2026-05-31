<?php

namespace App\Models;

use App\Support\AppNotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'clinic_id',
        'user_id',
        'type',
        'title',
        'message',
        'related_type',
        'related_id',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AppNotification $notification): void {
            if (($notification->getAttribute('clinic_id') ?? null) !== null && $notification->getAttribute('clinic_id') !== '') {
                return;
            }

            if ($notification->user_id) {
                $recipient = User::withoutGlobalScopes()->select(['id', 'clinic_id'])->find($notification->user_id);
                $notification->setAttribute(
                    'clinic_id',
                    $recipient?->clinic_id ?? config('tenancy.default_clinic_id')
                );
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopePlatformOnly($query)
    {
        return $query->whereIn('type', AppNotificationType::platformTypes());
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeClinicOnly($query)
    {
        return $query->whereNotIn('type', AppNotificationType::platformTypes());
    }
}
