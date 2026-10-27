<?php

namespace App\Policies;

use App\Models\Reading;
use App\Models\User;

class ReadingPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reading $reading): bool
    {
        return $user->id === $reading->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reading $reading): bool
    {
        return $user->id === $reading->user_id;
    }
}
