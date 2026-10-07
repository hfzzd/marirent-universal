<?php

namespace App\Policies;

use App\Models\Rental;
use App\Models\User;

class RentalPolicy
{
    /**
     * Determine whether the user can view the rental.
     */
    public function view(User $user, Rental $rental): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'user') {
            return (int) $rental->user_id === (int) $user->id;
        }

        if (in_array($user->role, ['owner', 'admin'], true)) {
            $ownerId = $user->role === 'owner' ? $user->id : $user->merchantId();
            return $ownerId !== 0 && (int) ($rental->vehicle?->owner_id ?? 0) === $ownerId;
        }

        return false;
    }

    /**
     * Determine whether the user can update the rental.
     */
    public function update(User $user, Rental $rental): bool
    {
        return $this->view($user, $rental);
    }

    /**
     * Determine whether the user can cancel the rental.
     */
    public function cancel(User $user, Rental $rental): bool
    {
        return $this->view($user, $rental);
    }
}