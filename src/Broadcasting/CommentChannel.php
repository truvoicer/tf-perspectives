<?php

namespace Truvoicer\TfPerspectives\Broadcasting;

use App\Models\User;

class CommentChannel
{
    public function join(User $user, $provider, $service, $itemId)
    {
        return true;
    }
}
