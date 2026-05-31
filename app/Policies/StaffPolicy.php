<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage staff');
    }

    public function view(User $user, Staff $staff): bool
    {
        return $user->can('manage staff')
            && (int) $user->clinic_id === (int) $staff->clinic_id;
    }

    public function create(User $user): bool
    {
        return $user->can('manage staff');
    }

    public function update(User $user, Staff $staff): bool
    {
        return $this->view($user, $staff);
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $this->view($user, $staff);
    }
}
