<?php
// packages/truvoicer/tf-perspectives/src/Http/Middleware/UsePerspectivesMorphMap.php

namespace Truvoicer\TfPerspectives\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;
use Truvoicer\TfPerspectives\Enums\MorphEntity;

class UsePerspectivesMorphMap
{
    public function handle(Request $request, Closure $next): Response
    {
        // ── 1. Swap the user model for this request ────────────────────
        $originalUserModel = Config::get('auth.providers.users.model');

        Config::set(
            'auth.providers.users.model',
            \Truvoicer\TfPerspectives\Models\User::class,
        );

        // Drop any cached guard instance so the next auth() call reads
        // through the updated config. Safe to call even if the guard was
        // never resolved — it just no-ops.
        Auth::forgetGuards();

        // ── 2. Override the morph map so `user` means the perspectives ──
        //    User for the duration of this request. This makes existing
        //    notifications/morph relations resolve against the right class.
        Relation::morphMap($this->map(), merge: true);

        try {
            return $next($request);
        } finally {
            // ── 3. Restore. This is essential — the same PHP worker will
            //    handle subsequent requests in Octane/Swoole, and config
            //    mutations otherwise leak across requests. Even in plain
            //    PHP-FPM it keeps the config correct for the lifecycle of
            //    any listeners fired during termination.
            Config::set('auth.providers.users.model', $originalUserModel);
            Auth::forgetGuards();
        }
    }

    private function map(): array
    {
        $map = [];

        foreach (MorphEntity::cases() as $case) {
            $map[$case->morphType()] = $case->value;
        }

        return $map;
    }
}
