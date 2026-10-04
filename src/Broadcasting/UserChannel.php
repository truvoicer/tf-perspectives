<?php

namespace Truvoicer\TfPerspectives\Broadcasting;

use Truvoicer\TfPerspectives\Models\User;

class UserChannel
{
    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user, mixed $userId)
    {
        return (int) $user->getId() === (int) $userId;
    }
}
