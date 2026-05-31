<?php

namespace App\Services;

/**
 * Primary dashboard experience key for role-aware layout sections.
 */
enum ClinicDashboardPersona: string
{
    case Admin = 'admin';
    case Doctor = 'doctor';
    case Receptionist = 'receptionist';
    case Accountant = 'accountant';

    public function labelKey(): string
    {
        return match ($this) {
            self::Admin => 'dashboard.persona_admin',
            self::Doctor => 'dashboard.persona_doctor',
            self::Receptionist => 'dashboard.persona_receptionist',
            self::Accountant => 'dashboard.persona_accountant',
        };
    }
}
