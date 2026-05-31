<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use App\Support\ClinicPermissions;

class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool
    {
        if ($user->can(ClinicPermissions::VIEW_ATTACHMENTS) || $user->can(ClinicPermissions::MANAGE_ATTACHMENTS)) {
            return $this->canAccessPatientContext($user, $attachment);
        }

        return false;
    }

    public function manage(User $user, Attachment $attachment): bool
    {
        if (! $user->can(ClinicPermissions::MANAGE_ATTACHMENTS)) {
            return false;
        }

        return $this->canAccessPatientContext($user, $attachment);
    }

    private function canAccessPatientContext(User $user, Attachment $attachment): bool
    {
        if ($user->isSuperAdmin() || $user->hasRole('admin')) {
            return true;
        }

        $attachment->loadMissing(['patient', 'visit']);
        $patient = $attachment->patient;
        if ($patient) {
            return app(PatientPolicy::class)->viewProfile($user, $patient);
        }

        if ($attachment->visit_id) {
            $visit = $attachment->visit;

            return $visit && app(VisitPolicy::class)->view($user, $visit);
        }

        return false;
    }
}
