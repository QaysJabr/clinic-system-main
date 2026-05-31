<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;
use App\Support\ClinicPermissions;

class InventoryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(ClinicPermissions::VIEW_INVENTORY)
            || $user->can(ClinicPermissions::MANAGE_INVENTORY);
    }

    public function view(User $user, InventoryItem $item): bool
    {
        return $this->viewAny($user)
            && (int) $user->clinic_id === (int) $item->clinic_id;
    }

    public function create(User $user): bool
    {
        return $user->can(ClinicPermissions::MANAGE_INVENTORY);
    }

    public function update(User $user, InventoryItem $item): bool
    {
        return $user->can(ClinicPermissions::MANAGE_INVENTORY)
            && (int) $user->clinic_id === (int) $item->clinic_id;
    }

    public function delete(User $user, InventoryItem $item): bool
    {
        return $this->update($user, $item);
    }
}
