<?php
// packages/truvoicer/tf-perspectives/src/Policies/ShoeDiscussionPolicy.php

namespace Truvoicer\TfPerspectives\Policies;

use Illuminate\Foundation\Auth\User;
use Truvoicer\TfPerspectives\Models\ShoeDiscussion;

class ShoeDiscussionPolicy
{
    public function update(User $user, ShoeDiscussion $discussion): bool
    {
        return $user->id === $discussion->user_id;
    }

    public function delete(User $user, ShoeDiscussion $discussion): bool
    {
        return $user->id === $discussion->user_id
            || $user->id === $discussion->shoe()->value('user_id');
    }
}
