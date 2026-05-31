<?php

namespace App\Enums;

enum AttachmentCategory: string
{
    case Document = 'document';
    case Lab = 'lab';
    case Radiology = 'radiology';
    case Prescription = 'prescription';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function labelKey(): string
    {
        return 'emr.attachment_category_'.$this->value;
    }

    public function isPreviewable(): bool
    {
        return true;
    }
}
