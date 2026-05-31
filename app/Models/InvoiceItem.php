<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClinic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use BelongsToClinic;

    protected $fillable = [
        'clinic_id',
        'invoice_id',
        'service_id',
        'item_name',
        'price',
        'quantity',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (InvoiceItem $item): void {
            if ($item->clinic_id || ! $item->invoice_id) {
                return;
            }

            $invoice = Invoice::query()->select(['id', 'clinic_id'])->find($item->invoice_id);
            if ($invoice) {
                $item->clinic_id = $invoice->clinic_id;
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
