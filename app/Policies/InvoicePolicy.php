<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Support\ClinicPermissions;

/**
 * Invoice access: permission gate + doctor may only access invoices tied to their doctor_id.
 */
class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ClinicPermissions::MANAGE_INVOICES);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if (! $user->can(ClinicPermissions::MANAGE_INVOICES)) {
            return false;
        }

        return $this->sameDoctorOrElevated($user, $invoice);
    }

    public function create(User $user): bool
    {
        return $user->can(ClinicPermissions::MANAGE_INVOICES);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if (! $user->can(ClinicPermissions::MANAGE_INVOICES)) {
            return false;
        }

        return $this->sameDoctorOrElevated($user, $invoice);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice);
    }

    private function sameDoctorOrElevated(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doctor = $user->linkedDoctor();
            if ($doctor === null) {
                return false;
            }

            if ($invoice->doctor_id === null) {
                return false;
            }

            return (int) $invoice->doctor_id === (int) $doctor->id;
        }

        return true;
    }
}
