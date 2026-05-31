<?php

namespace App\Enums;

enum ClinicalRecordType: string
{
    case Allergy = 'allergy';
    case ChronicCondition = 'chronic_condition';
    case Medication = 'medication';
    case FamilyHistory = 'family_history';
    case SurgicalHistory = 'surgical_history';
    case RiskFlag = 'risk_flag';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function labelKey(): string
    {
        return 'emr.clinical_'.$this->value;
    }
}
