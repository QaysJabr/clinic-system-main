<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('manage users');
    }

    public function view(User $actor, User $target): bool
    {
        if ($target->isSuperAdmin()) {
            return $actor->isSuperAdmin();
        }

        return $actor->can('manage users')
            && (int) $actor->clinic_id === (int) $target->clinic_id;
    }

    public function create(User $actor): bool
    {
        return $actor->can('manage users');
    }

    public function update(User $actor, User $target): bool
    {
        if ($target->isSuperAdmin()) {
            return false;
        }

        return $actor->can('manage users')
            && (int) $actor->clinic_id === (int) $target->clinic_id;
    }

    public function delete(User $actor, User $target): bool
    {
        return $this->update($actor, $target) && (int) $actor->id !== (int) $target->id;
    }
}
