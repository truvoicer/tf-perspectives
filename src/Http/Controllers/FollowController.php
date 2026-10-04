<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/FollowController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Http\Request;
use Truvoicer\TfPerspectives\Notifications\UserFollowed;

class FollowController extends Controller
{
    public function toggle(Request $request, string $handle)
    {
        $profile = \App\Models\User::where('handle', $handle)->firstOrFail();
        $me      = $request->user();

        abort_if($profile->id === $me->id, 422, 'You cannot follow yourself.');

        $existing = $me->following()->where('followed_id', $profile->id)->first();

        if ($existing) {
            $me->following()->detach($profile->id);
        } else {
            $me->following()->attach($profile->id);
            $profile->notify(new UserFollowed($me));
        }

        return redirect()->back();
    }
}
