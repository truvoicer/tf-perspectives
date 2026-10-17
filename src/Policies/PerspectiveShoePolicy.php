<?php
// packages/truvoicer/tf-perspectives/src/Policies/PerspectiveShoePolicy.php

namespace Truvoicer\TfPerspectives\Policies;

use Illuminate\Foundation\Auth\User;
use Truvoicer\TfPerspectives\Models\PerspectiveShoe;

class PerspectiveShoePolicy
{
    public function update(User $user, PerspectiveShoe $shoe): bool
    {
        return $user->id === $shoe->user_id;
    }

    public function delete(User $user, PerspectiveShoe $shoe): bool
    {
        return $user->id === $shoe->user_id;
    }

    /**
     * Only the perspective author can mark a shoe's impact. This is the
     * gate for the ranking feature when it ships.
     */
    public function markImpact(User $user, PerspectiveShoe $shoe): bool
    {
        return $user->id === $shoe->perspective()->value('user_id');
    }
}
