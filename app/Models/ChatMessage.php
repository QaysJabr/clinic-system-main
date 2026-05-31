<?php

namespace App\Models;

use App\Enums\ChatRoomType;
use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'sender_id',
        'receiver_id',
        'room_type',
        'patient_id',
        'invoice_id',
        'appointment_id',
        'body',
        'read_at',
        'edited_at',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'room_type' => ChatRoomType::class,
            'read_at' => 'datetime',
            'edited_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($this->isDeleted() || (int) $this->sender_id !== (int) $user->id) {
            return false;
        }

        return $this->created_at?->diffInMinutes(now()) <= 5;
    }

    /**
     * @return HasMany<ChatMessageHide, $this>
     */
    public function hides(): HasMany
    {
        return $this->hasMany(ChatMessageHide::class);
    }

    public function isHiddenFor(User $user): bool
    {
        return $this->hides()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function hideFor(User $user): void
    {
        $this->hides()->firstOrCreate(['user_id' => $user->id]);
    }

    /** إخفاء الرسالة من واجهة المستخدم الحالي فقط (لا حذف عالمي). */
    public function canBeHiddenBy(User $user): bool
    {
        if ($this->isDeleted() || $this->isHiddenFor($user)) {
            return false;
        }

        if ((int) $this->clinic_id !== (int) $user->clinic_id) {
            return false;
        }

        if ($this->room_type === ChatRoomType::Private) {
            return (int) $this->sender_id === (int) $user->id
                || (int) $this->receiver_id === (int) $user->id;
        }

        return true;
    }

    public function canBeDeletedBy(User $user): bool
    {
        return $this->canBeHiddenBy($user);
    }
}
